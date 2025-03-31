/**
 * Free Mini Games - Main Script File (DEPRECATED)
 * -----------------------------------------------
 * This file is deprecated and should no longer be used.
 * Its functionality has been replaced by:
 * - js/games-loader.js - For game loading and filtering
 * - js/main.js - For site-wide functionality
 * 
 * This file is kept for reference only and will be removed in future updates.
 * 
 * Last updated: 2024-04-01
 */

// This file is deprecated, see comment above

document.addEventListener('DOMContentLoaded', function() {
    // Game loading configuration
    const config = {
        gamesPerPage: 8,        // Number of games displayed per page
        currentPage: 0,         // Current page number
        isLoading: false,       // Whether it's currently loading
        allGamesLoaded: false,  // Whether all games have been loaded
        allGames: [],           // All game data
        filteredGames: [],      // Filtered game data
        currentCategory: 'all'  // Currently selected category
    };
    
    // Prevent style jittering, show content after all styles are loaded
    setTimeout(function() {
        document.body.classList.add('loaded');
    }, 100);
    
    // Initialize game data
    initGames();
    
    // Search functionality
    const searchForm = document.querySelector('.search-bar form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = this.querySelector('input[type="text"]').value.trim();
            if (searchTerm) {
                // Trigger search logic
                console.log('Search keyword:', searchTerm);
                searchGames(searchTerm);
            }
        });
    }
    
    // Category filtering functionality
    const categoryButtons = document.querySelectorAll('.categories-list button');
    if (categoryButtons.length) {
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to current button
                this.classList.add('active');
                
                const category = this.dataset.category;
                config.currentCategory = category;
                filterGamesByCategory(category);
            });
        });
    }
    
    // Scroll loading functionality
    window.addEventListener('scroll', throttle(checkScrollPosition, 100));
    
    // Add "Load More" button click event
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadNextPage();
        });
    }
    
    // Initialize game data
    function initGames() {
        // Get game data source
        const gameData = document.getElementById('game-data');
        
        // If game data not found, return
        if (!gameData) {
            console.error('Game data not found');
            return;
        }
        
        // Get all game cards
        const gameCards = gameData.querySelectorAll('.game-card');
        
        // If no game cards, show message
        if (!gameCards.length) {
            const gamesContainer = document.getElementById('games-container');
            if (gamesContainer) {
                gamesContainer.innerHTML = '<div class="no-games">No games available</div>';
            }
            return;
        }
        
        // Convert game cards to array
        config.allGames = Array.from(gameCards);
        config.filteredGames = [...config.allGames];
        
        // Initially load first page of games
        loadNextPage();
        
        console.log(`Initialization completed, total ${config.allGames.length} games`);
    }
    
    // Load next page of games
    function loadNextPage() {
        if (config.isLoading || config.allGamesLoaded) return;
        
        // Show loading indicator
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        // Hide load more button
        const loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = 'none';
        }
        
        config.isLoading = true;
        
        // Get current scroll position information
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = Math.max(
            document.body.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.clientHeight,
            document.documentElement.scrollHeight,
            document.documentElement.offsetHeight
        );
        
        // Simulate network delay to make loading effect more obvious
        setTimeout(function() {
            // Calculate start and end index of current page
            const startIndex = config.currentPage * config.gamesPerPage;
            const endIndex = startIndex + config.gamesPerPage;
            
            // Get games for current page
            const currentPageGames = config.filteredGames.slice(startIndex, endIndex);
            
            // If no more games, show all loaded
            if (currentPageGames.length === 0) {
                config.allGamesLoaded = true;
                
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                
                // Show no more games message
                const noMoreGames = document.getElementById('no-more-games');
                if (noMoreGames) {
                    noMoreGames.style.display = 'block';
                }
                
                config.isLoading = false;
                return;
            }
            
            // Get games container
            const gamesContainer = document.getElementById('games-container');
            if (!gamesContainer) {
                console.error('Games container not found');
                config.isLoading = false;
                return;
            }
            
            // If first page, clear container
            if (config.currentPage === 0) {
                gamesContainer.innerHTML = '';
            }
            
            // Add game cards to container
            currentPageGames.forEach(card => {
                const clone = card.cloneNode(true);
                gamesContainer.appendChild(clone);
            });
            
            // Increment page number
            config.currentPage++;
            
            // Check if all games have been loaded
            if (endIndex >= config.filteredGames.length) {
                config.allGamesLoaded = true;
                
                // Show no more games message
                const noMoreGames = document.getElementById('no-more-games');
                if (noMoreGames) {
                    noMoreGames.style.display = 'block';
                }
            } else {
                // If more games, show load more button
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = 'block';
                }
            }
            
            // Hide loading indicator
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            config.isLoading = false;
            
            // Trigger scroll event to check if more loading needed
            if (documentHeight - (scrollTop + windowHeight) < 200) {
                // Prevent immediate loading of next page
                setTimeout(function() {
                    checkScrollPosition();
                }, 500);
            }
            
            console.log(`Loaded page ${config.currentPage}, with ${currentPageGames.length} games`);
        }, 500); // 500ms delay to make loading effect more obvious
    }
    
    // Check scroll position
    function checkScrollPosition() {
        if (config.isLoading || config.allGamesLoaded) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = Math.max(
            document.body.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.clientHeight,
            document.documentElement.scrollHeight,
            document.documentElement.offsetHeight
        );
        
        // Lower trigger threshold, load more when scrolled to 500px from bottom
        if (documentHeight - (scrollTop + windowHeight) < 500) {
            loadNextPage();
        }
    }
    
    // Throttle function - limit function call frequency
    function throttle(func, delay) {
        let lastCall = 0;
        return function(...args) {
            const now = new Date().getTime();
            if (now - lastCall < delay) {
                return;
            }
            lastCall = now;
            return func(...args);
        };
    }
    
    // Search function
    function searchGames(keyword) {
        // Reset state
        config.currentPage = 0;
        config.allGamesLoaded = false;
        
        // Hide no more games message
        const noMoreGames = document.getElementById('no-more-games');
        if (noMoreGames) {
            noMoreGames.style.display = 'none';
        }
        
        // Filter games containing keyword
        const lowercaseKeyword = keyword.toLowerCase();
        config.filteredGames = config.allGames.filter(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            return title.includes(lowercaseKeyword);
        });
        
        // Reload games
        loadNextPage();
        
        // If no games found, show message
        if (config.filteredGames.length === 0) {
            const gamesContainer = document.getElementById('games-container');
            if (gamesContainer) {
                gamesContainer.innerHTML = `<div class="no-games">No games found containing "${keyword}"</div>`;
            }
        }
        
        console.log(`Search "${keyword}", found ${config.filteredGames.length} matching games`);
    }
    
    // Category filtering function
    function filterGamesByCategory(category) {
        // Reset state
        config.currentPage = 0;
        config.allGamesLoaded = false;
        
        // Hide no more games message
        const noMoreGames = document.getElementById('no-more-games');
        if (noMoreGames) {
            noMoreGames.style.display = 'none';
        }
        
        // Filter games by specified category
        if (category === 'all') {
            config.filteredGames = [...config.allGames];
        } else {
            config.filteredGames = config.allGames.filter(card => {
                return card.dataset.category === category;
            });
        }
        
        // Reload games
        loadNextPage();
        
        // If no games found, show message
        if (config.filteredGames.length === 0) {
            const gamesContainer = document.getElementById('games-container');
            if (gamesContainer) {
                gamesContainer.innerHTML = `<div class="no-games">No ${getCategoryName(category)} games found</div>`;
            }
        }
        
        console.log(`Filter category: ${category}, found ${config.filteredGames.length} games`);
    }
    
    // Get category name
    function getCategoryName(category) {
        const categoryMap = {
            'action': 'Action',
            'puzzle': 'Puzzle',
            'racing': 'Racing',
            'sports': 'Sports',
            'strategy': 'Strategy',
            'horror': 'Horror',
            'adventure': 'Adventure',
            'casual': 'Casual'
        };
        
        return categoryMap[category] ? categoryMap[category] : '';
    }
});

// Fix potential style issues
window.addEventListener('load', function() {
    // Ensure header styles are correctly applied
    const header = document.querySelector('.site-header');
    if (header) {
        // Force layout update
        header.style.display = 'block';
        setTimeout(function() {
            header.style.display = '';
        }, 10);
    }
    
    // Ensure search bar styles are correct
    const searchBar = document.querySelector('.search-bar');
    if (searchBar) {
        // Force layout update
        searchBar.style.display = 'block';
        setTimeout(function() {
            searchBar.style.display = '';
        }, 10);
    }
}); 