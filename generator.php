<?php
/**
 * Portfolio Analyzer - Simplified Functional Version
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

/**
 * Custom debug logger function that writes to a file in the project directory
 * @param string $message The message to log
 * @param bool $clear Whether to clear the log file first (used on first call)
 */
function debug_log($message, $clear = false) {
    return; // Disable debug logging for production use

    static $firstCall = true;
    $logFile = __DIR__ . '/debug.log';
    
    // Clear the log file on the first call if requested
    if ($firstCall && $clear) {
        file_put_contents($logFile, "");
        $firstCall = false;
    }
    
    // Append the message to the log file
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

/**
 * Get the latest stock price from Yahoo Finance
 */
function getLatestPrice($code) {
    try {
        $client = new Client();
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$code}.JK";
        $response = $client->request('GET', $url);
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['chart']['result'][0]['meta']['regularMarketPrice'] ?? 0;
    } catch (Exception $e) {
        echo "Could not fetch price for {$code}: " . $e->getMessage() . "\n";
        debug_log("Price fetch error for {$code}: " . $e->getMessage());
        return 0;
    }
}

/**
 * Load and parse transactions from CSV
 * @return array Parsed transaction data
 */
function loadTransactions($filePath) {
    $transactions = [];
    
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle, 1000, ","); // Skip header row
        
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            // Parse amount
            $amountStr = str_replace('.', '', $row[3]);
            $amountStr = str_replace(',', '.', $amountStr);
            if (strpos($amountStr, '(') !== false) {
                $amount = -floatval(preg_replace('/[\(\)]/', '', $amountStr));
            } else {
                $amount = floatval($amountStr);
            }
            
            // Add parsed transaction
            $transactions[] = [
                'date' => $row[0], // Store the date
                'type' => $row[1],
                'code' => $row[2],
                'amount' => $amount,
                'lot' => !empty($row[4]) ? intval($row[4]) : 0,
                'price' => !empty($row[5]) ? intval($row[5]) : 0
            ];
        }
        fclose($handle);
    }
    
    return $transactions;
}

/**
 * Build portfolio data from transactions
 * @return array Portfolio data and grand total
 */
function buildPortfolio($transactions) {
    // Collect portfolio data
    $portfolio = array_reduce($transactions, function($acc, $trx) {
        if ($trx['type'] === 'transaction' && !empty($trx['code'])) {
            $code = $trx['code'];
            
            if (!isset($acc[$code])) {
                $acc[$code] = [
                    'total_lot' => 0,
                    'weighted_price_sum' => 0
                ];
            }
            
            if ($trx['amount'] > 0) { // Sell
                $acc[$code]['total_lot'] -= $trx['lot'];
                $acc[$code]['weighted_price_sum'] -= $trx['lot'] * $trx['price'];
            } else { // Buy
                $acc[$code]['total_lot'] += $trx['lot'];
                $acc[$code]['weighted_price_sum'] += $trx['lot'] * $trx['price'];
            }
        }
        return $acc;
    }, []);
    
    // Calculate values, fetch latest prices
    $grandTotalValue = 0;
    $portfolioItems = [];
    
    foreach ($portfolio as $code => $data) {
        $totalLot = $data['total_lot'];
        if ($totalLot <= 0) continue; // Skip stocks with no lots
        
        $avgPrice = $totalLot > 0 ? $data['weighted_price_sum'] / $totalLot : 0;
        $totalValue = $totalLot * $avgPrice * 100; // 100 shares per lot
        $latestPrice = getLatestPrice($code);
        $profit = ($avgPrice > 0) ? (($latestPrice - $avgPrice) / $avgPrice) * 100 : 0;
        
        $grandTotalValue += $totalValue;
        
        $portfolioItems[$code] = [
            'code' => $code,
            'total_lot' => $totalLot,
            'avg_price' => intval($avgPrice),
            'total_value' => intval($totalValue),
            'latest_price' => intval($latestPrice),
            'profit' => number_format($profit, 2),
            'ratio' => 0 // Will calculate after grand total is known
        ];
    }
    
    // Calculate ratios
    if ($grandTotalValue > 0) {
        foreach ($portfolioItems as $code => $item) {
            $portfolioItems[$code]['ratio'] = number_format(($item['total_value'] / $grandTotalValue) * 100, 2);
        }
    }
    
    return ['items' => array_values($portfolioItems), 'grand_total' => $grandTotalValue];
}

/**
 * Build profit metrics from transactions and portfolio
 * @return array Profit metrics
 */
function buildProfit($transactions, $portfolio) {
    // Calculate dividend and topup
    $dividend = 0;
    $topup = 0;
    
    foreach ($transactions as $trx) {
        if ($trx['type'] === 'dividen') {
            $dividend += $trx['amount'];
        } elseif ($trx['type'] === 'topup') {
            $topup += $trx['amount'];
        }
    }
    
    // Calculate realized P/L
    $holdings = [];
    $realizedPL = 0;
    
    foreach ($transactions as $trx) {
        if ($trx['type'] === 'transaction' && !empty($trx['code'])) {
            $code = $trx['code'];
            
            if (!isset($holdings[$code])) {
                $holdings[$code] = ['lots' => 0, 'cost' => 0];
            }
            
            if ($trx['amount'] < 0) { // Buy
                $holdings[$code]['cost'] += abs($trx['lot'] * $trx['price']);
                $holdings[$code]['lots'] += $trx['lot'];
            } elseif ($trx['amount'] > 0 && $trx['lot'] > 0) { // Sell
                if ($holdings[$code]['lots'] > 0) {
                    $avgBuy = $holdings[$code]['cost'] / $holdings[$code]['lots'];
                    $realizedPL += ($trx['price'] - $avgBuy) * $trx['lot'] * 100;
                    $holdings[$code]['lots'] -= $trx['lot'];
                    $holdings[$code]['cost'] -= $avgBuy * $trx['lot'];
                }
            }
        }
    }
    
    // Calculate unrealized P/L
    $totalUnrealizedPL = 0;
    $totalValue = 0;
    
    foreach ($portfolio['items'] as $item) {
        $unrealizedPL = ($item['latest_price'] - $item['avg_price']) * $item['total_lot'] * 100;
        $totalUnrealizedPL += $unrealizedPL;
        $totalValue += $item['total_value'];
    }
    
    // Calculate percentages
    $dividendPercent = ($topup > 0) ? number_format(($dividend / $topup) * 100, 2) : '0.00';
    $unrealizedPLPercent = ($totalValue > 0) ? number_format(($totalUnrealizedPL / $totalValue) * 100, 2) : '0.00';
    $realizedPLPercent = ($topup > 0) ? number_format(($realizedPL / $topup) * 100, 2) : '0.00';
    
    // Calculate total profit
    $totalProfitValue = $totalUnrealizedPL + $realizedPL + $dividend;
    $totalProfitPercent = ($topup > 0) ? 
        number_format(($totalProfitValue / $topup) * 100, 2) : '0.00';
    
    return [
        'dividend' => $dividendPercent,
        'unrealized_pl' => $unrealizedPLPercent,
        'realized_pl' => $realizedPLPercent,
        'total_profit' => $totalProfitPercent
    ];
}

/**
 * Build fund metrics (asset allocation)
 * @return array Fund metrics showing investment allocation
 */
function buildFund($transactions, $portfolio) {
    // Calculate total topup
    $topup = array_reduce($transactions, function($sum, $trx) {
        return $trx['type'] === 'topup' ? $sum + $trx['amount'] : $sum;
    }, 0);
    
    // Calculate percentages
    $totalInvestedPercent = ($topup > 0) ? 
        number_format(($portfolio['grand_total'] / $topup) * 100, 2) : '0.00';
    $totalCashPercent = ($topup > 0) ? 
        number_format((($topup - $portfolio['grand_total']) / $topup) * 100, 2) : '0.00';
    
    return [
        'totalInvested' => $totalInvestedPercent,
        'totalCash' => $totalCashPercent
    ];
}

/**
 * Build activity feed from transactions
 * @return array Activity entries with date and description
 */
function buildActivity($transactions) {
    debug_log("\n========== STARTING ACTIVITY BUILD ==========", true); // Clear log on first call
    debug_log("Total transactions to process: " . count($transactions));
    
    // Create a map to track stock holdings over time
    $stockHistory = [];
    
    // First, calculate stock values at each point in time
    // Process transactions in chronological order (as they come)
    foreach ($transactions as $trx) {
        $date = $trx['date']; // Already in the format dd/mm/yyyy
        $code = $trx['code'] ?? null;
        
        if ($trx['type'] === 'transaction' && !empty($code)) {
            if (!isset($stockHistory[$code])) {
                $stockHistory[$code] = [];
            }
            
            // Get the latest state for this stock
            $latest = end($stockHistory[$code]) ?: ['date' => '', 'lots' => 0, 'cost' => 0];
            $newState = [
                'date' => $date,
                'lots' => $latest['lots'],
                'cost' => $latest['cost']
            ];
            
            if ($trx['amount'] < 0) { // Buy
                $newState['lots'] += $trx['lot'];
                $newState['cost'] += abs($trx['lot'] * $trx['price']);
            } else { // Sell
                if ($newState['lots'] > 0) {
                    // Calculate proportion of lots being sold
                    $proportion = min(1, $trx['lot'] / $newState['lots']);
                    // Reduce cost proportionally
                    $costReduction = $newState['cost'] * $proportion;
                    $newState['cost'] -= $costReduction;
                    $newState['lots'] -= $trx['lot'];
                }
            }
            
            // Record this state
            $stockHistory[$code][] = $newState;
            
            // Debug log for transaction processing
            debug_log("Updated stock history for {$code} on {$date}:");
            debug_log("  Action: " . ($trx['amount'] < 0 ? "BUY" : "SELL"));
            debug_log("  Lots: {$newState['lots']}, Cost: {$newState['cost']}");
            if ($newState['lots'] > 0) {
                $avgCost = $newState['cost'] / $newState['lots'];
                debug_log("  Avg Cost/Lot: {$avgCost}");
                debug_log("  Total Value: " . ($avgCost * $newState['lots'] * 100));
            }
        }
    }
    
    // Process dividends and create activity entries
    $activities = [];
    $transactions = array_reverse($transactions); // Most recent first for display
    
    foreach ($transactions as $trx) {
        $description = '';
        
        switch ($trx['type']) {
            case 'topup':
                $description = "melakukan <strong>topup</strong> modal";
                break;
                
            case 'dividen':
                $yield = '0%';
                $code = $trx['code'] ?? '';
                
                if (!empty($code) && isset($stockHistory[$code])) {
                    // Find the state of this stock right BEFORE the dividend date
                    $dividendDate = $trx['date'];
                    $relevantState = null;
                    
                    // Convert dividend date to timestamp for comparison
                    $divDateParts = explode('/', $dividendDate);
                    if (count($divDateParts) === 3) {
                        $divTimestamp = mktime(0, 0, 0, $divDateParts[1], $divDateParts[0], $divDateParts[2]);
                        
                        // Find the last state before this dividend
                        foreach ($stockHistory[$code] as $state) {
                            $stateParts = explode('/', $state['date']);
                            if (count($stateParts) === 3) {
                                $stateTimestamp = mktime(0, 0, 0, $stateParts[1], $stateParts[0], $stateParts[2]);
                                
                                // If this state is after the dividend, stop looking
                                if ($stateTimestamp > $divTimestamp) {
                                    break;
                                }
                                
                                // Only use states where we actually own the stock
                                if ($state['lots'] > 0) {
                                    $relevantState = $state;
                                }
                            }
                        }
                    }
                    
                    // If we couldn't find a state before the dividend, 
                    // try the earliest transaction where we owned this stock
                    if (!$relevantState && count($stockHistory[$code]) > 0) {
                        // Find the first state where we owned some of this stock
                        $firstBuyState = null;
                        foreach ($stockHistory[$code] as $state) {
                            if ($state['lots'] > 0) {
                                $firstBuyState = $state;
                                // Don't break - we want to find the earliest buy
                            }
                        }
                        
                        if ($firstBuyState) {
                            $relevantState = $firstBuyState;
                        }
                    }
                    
                    // Calculate yield with detailed logging
                    if ($relevantState && $relevantState['lots'] > 0) {
                        $avgPrice = $relevantState['cost'] / $relevantState['lots'];
                        $stockValue = $avgPrice * $relevantState['lots'] * 100; // 100 shares per lot
                        
                        if ($stockValue > 0) {
                            $yieldValue = ($trx['amount'] / $stockValue) * 100;
                            $yield = number_format($yieldValue, 2) . '%';
                            
                            // Debug information for manual verification
                            debug_log("\n====== DIVIDEND YIELD CALCULATION FOR {$code} ======");
                            debug_log("Date: {$trx['date']}");
                            debug_log("Dividend Amount: {$trx['amount']}");
                            debug_log("Lots at time of dividend: {$relevantState['lots']}");
                            debug_log("Average Price: {$avgPrice}");
                            debug_log("Total Stock Value: {$stockValue}");
                            debug_log("Yield Calculation: {$trx['amount']} / {$stockValue} * 100 = {$yieldValue}%");
                            debug_log("Formatted Yield: {$yield}");
                            debug_log("State Date Used: {$relevantState['date']}");
                            debug_log("===============================================");
                        }
                    } else {
                        // Debug why yield calculation failed
                        debug_log("\n====== DIVIDEND YIELD CALCULATION FAILED FOR {$code} ======");
                        debug_log("Date: {$trx['date']}");
                        debug_log("Dividend Amount: {$trx['amount']}");
                        debug_log("Has Relevant State: " . ($relevantState ? "Yes" : "No"));
                        if ($relevantState) {
                            debug_log("Lots in Relevant State: {$relevantState['lots']}");
                            debug_log("State Date: {$relevantState['date']}");
                        } else {
                            debug_log("No relevant state found - could not find stock ownership before dividend");
                        }
                        debug_log("===============================================");
                    }
                }
                
                $description = "mendapatkan <strong>dividen</strong> dari <strong>{$code}</strong> sebesar $yield";
                break;
                
            case 'transaction':
                if ($trx['amount'] < 0) {
                    $description = "melakukan <strong>pembelian</strong> saham <strong>{$trx['code']}</strong>";
                } else {
                    $description = "melakukan <strong>penjualan</strong> saham <strong>{$trx['code']}</strong>";
                }
                break;
        }
        
        $activities[] = [
            'time' => $trx['date'],
            'description' => $description
        ];
    }
    
    return $activities;
}

/**
 * Generate output JSON
 */
function generateOutput($portfolio, $profitData, $fundData, $activityData) {
    // Log a final summary
    debug_log("\n========== GENERATION SUMMARY ==========");
    debug_log("Portfolio items: " . count($portfolio['items']));
    debug_log("Grand total: " . $portfolio['grand_total']);
    debug_log("Dividend yield: " . $profitData['dividend'] . "%");
    debug_log("Total profit: " . $profitData['total_profit'] . "%");
    debug_log("Activity entries: " . count($activityData));
    debug_log("========== END SUMMARY ==========\n");
    
    $output = [
        'portfolio' => array_map(function($item) {
            return [
                'code' => $item['code'],
                'avg_price' => $item['avg_price'],
                'latest_price' => $item['latest_price'],
                'profit' => $item['profit'],
                'ratio' => $item['ratio']
            ];
        }, $portfolio['items']),
        'grand_total' => $portfolio['grand_total'],
        'profit' => $profitData,
        'funds' => $fundData,
        'activity' => $activityData,
        'last_update' => date('H:i d/m/Y')
    ];
    
    $outputJson = json_encode($output, JSON_PRETTY_PRINT);
    $publicPath = __DIR__ . '/public/data.json';
    $distPath = __DIR__ . '/dist/data.json';
    file_put_contents($publicPath, $outputJson);
    echo "Portfolio data saved to public/data.json\n";
    // Also write to dist/data.json if dist exists
    if (is_dir(__DIR__ . '/dist')) {
        file_put_contents($distPath, $outputJson);
        echo "Portfolio data also saved to dist/data.json\n";
    }
}

// --- Main execution ---
$transactions = loadTransactions(__DIR__ . '/resources/transaction.csv');
$portfolio = buildPortfolio($transactions);
$profitData = buildProfit($transactions, $portfolio);
$fundData = buildFund($transactions, $portfolio);
$activityData = buildActivity($transactions); // Updated to only use transactions
generateOutput($portfolio, $profitData, $fundData, $activityData);
