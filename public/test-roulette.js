// Copy this entire script and paste it into the browser console on the roulette page
// Then run: testRoulette(2660)

function testRoulette(totalSpins) {
    console.log('=== STARTING ROULETTE TEST ===');
    console.log('Total spins: ' + totalSpins);
    console.log('');
    
    // Create a deep copy of products to avoid modifying the original
    var testProducts = JSON.parse(JSON.stringify(products));
    
    var results = {};
    var tierResults = {
        'rare': 0,
        'medium': 0,
        'common': 0
    };
    
    // Initialize results
    testProducts.forEach(function(product, index) {
        results[product.id] = {
            name: product.name,
            tier: product.tier,
            allocation: product.allocation,
            available: product.allocation,
            count: 0
        };
    });
    
    // Spin function
    function testSpin() {
        var tierPercentages = {
            'rare': 10,
            'medium': 30,
            'common': 60
        };
        
        var tierRandom = Math.random() * 100;
        var selectedTier;
        
        if (tierRandom < tierPercentages.rare) {
            selectedTier = 'rare';
        } else if (tierRandom < tierPercentages.rare + tierPercentages.medium) {
            selectedTier = 'medium';
        } else {
            selectedTier = 'common';
        }
        
        var tierProducts = testProducts.filter(function(product) {
            return product.tier === selectedTier && product.available > 0;
        });
        
        if (tierProducts.length === 0) {
            var tierOrder = ['common', 'medium', 'rare'];
            for (var i = 0; i < tierOrder.length; i++) {
                if (tierOrder[i] !== selectedTier) {
                    tierProducts = testProducts.filter(function(product) {
                        return product.tier === tierOrder[i] && product.available > 0;
                    });
                    
                    if (tierProducts.length > 0) {
                        selectedTier = tierOrder[i];
                        break;
                    }
                }
            }
        }
        
        if (tierProducts.length === 0) {
            return null;
        }
        
        var selectedProduct = tierProducts.reduce(function(max, product) {
            return product.available > max.available ? product : max;
        }, tierProducts[0]);
        
        selectedProduct.available--;
        tierResults[selectedTier]++;
        
        return selectedProduct;
    }
    
    // Run all spins
    for (var i = 0; i < totalSpins; i++) {
        var selected = testSpin();
        if (selected) {
            results[selected.id].count++;
            results[selected.id].available--;
        }
    }
    
    // Display results
    console.log('=== TIER DISTRIBUTION ===');
    console.log('Rare (Expected 10%): ' + tierResults.rare + ' (' + ((tierResults.rare / totalSpins) * 100).toFixed(2) + '%)');
    console.log('Medium (Expected 30%): ' + tierResults.medium + ' (' + ((tierResults.medium / totalSpins) * 100).toFixed(2) + '%)');
    console.log('Common (Expected 60%): ' + tierResults.common + ' (' + ((tierResults.common / totalSpins) * 100).toFixed(2) + '%)');
    console.log('');
    
    console.log('=== LOCKER DISTRIBUTION ===');
    console.log('Locker | Tier    | Allocation | Selected | Percentage | Remaining');
    console.log('-------|---------|------------|----------|------------|----------');
    
    Object.keys(results).forEach(function(key) {
        var result = results[key];
        var percentage = ((result.count / totalSpins) * 100).toFixed(2);
        var tierPadded = (result.tier + '        ').substring(0, 7);
        var allocationPadded = ('      ' + result.allocation).slice(-10);
        var countPadded = ('        ' + result.count).slice(-8);
        var percentPadded = ('       ' + percentage + '%').slice(-10);
        var remainingPadded = ('         ' + result.available).slice(-9);
        
        console.log(
            ('  ' + result.name).slice(-4) + '   | ' + 
            tierPadded + ' | ' + 
            allocationPadded + ' | ' + 
            countPadded + ' | ' + 
            percentPadded + ' | ' + 
            remainingPadded
        );
    });
    
    console.log('');
    console.log('=== SUMMARY ===');
    console.log('Total spins completed: ' + totalSpins);
    console.log('Total items distributed: ' + Object.keys(results).reduce(function(sum, key) { return sum + results[key].count; }, 0));
    
    // Calculate which lockers ran out
    var emptyLockers = Object.keys(results).filter(function(key) { return results[key].available === 0; });
    if (emptyLockers.length > 0) {
        console.log('');
        console.log('Lockers that ran out of stock: ' + emptyLockers.map(function(key) { return results[key].name; }).join(', '));
    }
    
    console.log('');
    console.log('=== TEST COMPLETE ===');
    
    return results;
}

console.log('Test function loaded! Run: testRoulette(2660)');
