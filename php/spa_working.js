console.log('SPA working script loaded');

class StreamingSPA {
    constructor() {
        console.log('StreamingSPA constructor called');
        this.currentRoute = 'home';
        this.currentListId = null;
        this.currentVideoId = null;
        this.playlistVideos = [];
        this.currentVideoIndex = -1;
        this.userLists = [];
        this.currentUserId = null;
        
        this.init();
    }
    
    init() {
        console.log('init() called');
        this.setupRouting();
        this.setupEventListeners();
        this.loadInitialData();
    }
    
    setupRouting() {
        console.log('setupRouting() called');
        // Handle navigation clicks
        document.querySelectorAll('[data-route]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const route = e.target.getAttribute('data-route');
                this.navigateTo(route);
            });
        });
    }
    
    setupEventListeners() {
        console.log('setupEventListeners() called');
        
        // Profile search button - use event delegation for dynamic content
        document.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'search-profiles-btn') {
                console.log('Profile search button clicked via delegation');
                e.preventDefault();
                e.stopPropagation();
                this.searchProfiles();
            }
        });
    }
    
    loadInitialData() {
        console.log('loadInitialData() called');
        // For now, just log that we're loading data
    }
    
    navigateTo(route, updateHistory = true) {
        console.log('navigateTo called with route:', route);
        // Hide all containers
        document.querySelectorAll('.spa-container').forEach(container => {
            container.classList.remove('active');
        });

        // Show target container
        const targetContainer = document.getElementById(route);
        if (targetContainer) {
            targetContainer.classList.add('active');
            this.currentRoute = route;
            
            if (updateHistory) {
                history.pushState({ route }, '', `#${route}`);
            }
        }
    }
    
    async searchProfiles() {
        console.log('searchProfiles method called');
        const queryInput = document.getElementById('profile-search-query');
        console.log('Query input element:', queryInput);
        
        if (!queryInput) {
            console.error('Profile search query input not found');
            this.showError('Search input not found');
            return;
        }
        
        const query = queryInput.value.trim();
        console.log('Search query:', query);
        
        if (!query) {
            this.showError('Please enter a search query');
            return;
        }

        console.log('Starting search...');
        this.showLoading();
        
        try {
            const response = await fetch(`api/search_profiles.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            this.hideLoading();
            
            if (data.success) {
                this.renderProfileSearchResults(data.profiles);
            } else {
                this.showError(data.message || 'Failed to search profiles');
            }
        } catch (error) {
            console.error('Error searching profiles:', error);
            this.hideLoading();
            this.showError('Failed to search profiles');
        }
    }
    
    renderProfileSearchResults(profiles) {
        console.log('renderProfileSearchResults called with:', profiles);
        const container = document.getElementById('profile-search-results');
        if (!container) {
            console.error('profile-search-results container not found');
            return;
        }
        
        if (profiles.length === 0) {
            container.innerHTML = '<p class="muted">No users found matching your search.</p>';
            return;
        }
        
        container.innerHTML = profiles.map(profile => `
            <div class="list-card">
                <h3 class="list-name">${profile.first_name} ${profile.last_name}</h3>
                <p class="muted">@${profile.username}</p>
                <p class="muted">${profile.public_lists_count} public lists</p>
                <div class="list-actions">
                    <button class="btn btn-primary" data-action="view-user-profile" data-user-id="${profile.id}">
                        View Profile
                    </button>
                    ${profile.is_following ? 
                        `<button class="btn btn-secondary" data-action="unfollow-user" data-user-id="${profile.id}">Unfollow</button>` :
                        `<button class="btn btn-primary" data-action="follow-user" data-user-id="${profile.id}">Follow</button>`
                    }
                </div>
            </div>
        `).join('');
    }
    
    showLoading() {
        console.log('showLoading called');
        // Simple loading implementation
        const container = document.getElementById('profile-search-results');
        if (container) {
            container.innerHTML = '<p>Loading...</p>';
        }
    }
    
    hideLoading() {
        console.log('hideLoading called');
        // Loading will be hidden when results are rendered
    }
    
    showError(message) {
        console.log('showError called with:', message);
        const container = document.getElementById('profile-search-results');
        if (container) {
            container.innerHTML = `<p class="error">${message}</p>`;
        }
    }
}

console.log('StreamingSPA class defined');

// Initialize SPA when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded event fired');
    try {
        console.log('About to create StreamingSPA instance...');
        window.spa = new StreamingSPA();
        console.log('SPA initialized successfully:', window.spa);
        
        // Add global test function for profile search
        window.testProfileSearch = function() {
            console.log('Testing profile search...');
            if (window.spa && window.spa.searchProfiles) {
                window.spa.searchProfiles();
            } else {
                console.error('SPA or searchProfiles method not found');
            }
        };
    } catch (error) {
        console.error('Error initializing SPA:', error);
        console.error('Error details:', error.message);
        console.error('Error stack:', error.stack);
    }
});

// Also try to initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    console.log('DOM is still loading, waiting for DOMContentLoaded');
} else {
    console.log('DOM is already loaded, initializing SPA immediately');
    try {
        window.spa = new StreamingSPA();
        console.log('SPA initialized immediately:', window.spa);
        
        // Add global test function for profile search
        window.testProfileSearch = function() {
            console.log('Testing profile search...');
            if (window.spa && window.spa.searchProfiles) {
                window.spa.searchProfiles();
            } else {
                console.error('SPA or searchProfiles method not found');
            }
        };
    } catch (error) {
        console.error('Error initializing SPA immediately:', error);
    }
}
