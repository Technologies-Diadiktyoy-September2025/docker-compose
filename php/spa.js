/**
 * Single Page Application (SPA) for Streaming Content Management
 */

class StreamingSPA {
    constructor() {
        this.currentRoute = 'home';
        this.currentListId = null;
        this.currentVideoId = null;
        this.playlistVideos = [];
        this.currentVideoIndex = -1;
        this.userLists = [];
        
        this.init();
    }

    init() {
        this.setupRouting();
        this.setupEventListeners();
        this.loadInitialData();
    }

    setupRouting() {
        // Handle navigation clicks
        document.querySelectorAll('[data-route]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const route = e.target.getAttribute('data-route');
                this.navigateTo(route);
            });
        });

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            const route = e.state?.route || 'home';
            this.navigateTo(route, false);
        });

        // Set initial route
        const hash = window.location.hash.substring(1);
        if (hash) {
            this.handleRoute(hash, false);
        }
    }

    setupEventListeners() {
        // Create list form
        document.getElementById('create-list-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.createList();
        });

        // Search form
        document.getElementById('search-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.searchVideos();
        });

        // Modal close
        document.querySelector('.close').addEventListener('click', () => {
            this.closeModal();
        });

        // Click outside modal to close
        document.getElementById('modal').addEventListener('click', (e) => {
            if (e.target.id === 'modal') {
                this.closeModal();
            }
        });

        // List action buttons
        document.addEventListener('click', (e) => {
            console.log('Click detected on:', e.target, 'with data-action:', e.target.getAttribute('data-action'));
            
            if (e.target.matches('[data-action="view-list"]')) {
                e.preventDefault();
                console.log('View list clicked, ID:', e.target.dataset.listId);
                this.viewList(e.target.dataset.listId);
            } else if (e.target.matches('[data-action="edit-list"]')) {
                e.preventDefault();
                console.log('Edit list clicked, ID:', e.target.dataset.listId);
                this.editList(e.target.dataset.listId);
            } else if (e.target.matches('[data-action="delete-list"]')) {
                e.preventDefault();
                console.log('Delete list clicked, ID:', e.target.dataset.listId);
                this.deleteList(e.target.dataset.listId);
            } else if (e.target.matches('[data-action="play-video"]')) {
                e.preventDefault();
                console.log('Play video clicked, ID:', e.target.dataset.videoId);
                this.playVideo(e.target.dataset.videoId, e.target.dataset.listId);
            } else if (e.target.matches('[data-action="add-to-list"]')) {
                e.preventDefault();
                console.log('Add to list clicked, ID:', e.target.dataset.videoId);
                this.addVideoToList(e.target.dataset.videoId, e.target.dataset.listId);
            } else if (e.target.matches('[data-action="remove-from-list"]')) {
                e.preventDefault();
                console.log('Remove from list clicked, ID:', e.target.dataset.itemId);
                this.removeFromPlaylist(e.target.dataset.itemId, e.target.dataset.listId);
            } else if (e.target.matches('[data-action="add-content"]')) {
                e.preventDefault();
                console.log('Add content clicked, List ID:', e.target.dataset.listId);
                this.showAddContentForm(e.target.dataset.listId);
            } else if (e.target.id === 'search-profiles-btn') {
                e.preventDefault();
                console.log('Search profiles button clicked');
                this.searchProfiles();
            } else if (e.target.matches('[data-action="view-user-profile"]')) {
                e.preventDefault();
                console.log('View user profile clicked, ID:', e.target.dataset.userId);
                this.viewUserProfile(e.target.dataset.userId);
            } else if (e.target.matches('[data-action="follow-user"]')) {
                e.preventDefault();
                console.log('Follow user clicked, ID:', e.target.dataset.userId);
                this.followUser(e.target.dataset.userId);
            } else if (e.target.matches('[data-action="unfollow-user"]')) {
                e.preventDefault();
                console.log('Unfollow user clicked, ID:', e.target.dataset.userId);
                this.unfollowUser(e.target.dataset.userId);
            }
        });
    }

    handleRoute(route, updateHistory = true) {
        // Handle complex routes like list/[id] or user-profile/[id]
        if (route.startsWith('list/')) {
            const listId = route.split('/')[1];
            this.viewList(listId);
            return;
        } else if (route.startsWith('user-profile/')) {
            const userId = route.split('/')[1];
            this.viewUserProfile(userId);
            return;
        } else {
            // Handle simple routes
            this.navigateTo(route, updateHistory);
        }
    }

    navigateTo(route, updateHistory = true) {
        // Hide all containers
        document.querySelectorAll('.spa-container').forEach(container => {
            container.classList.remove('active');
        });

        // Show target container
        const targetContainer = document.getElementById(route);
        if (targetContainer) {
            targetContainer.classList.add('active');
            this.currentRoute = route;

            // Update navigation
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-route') === route) {
                    link.classList.add('active');
                }
            });

            // Load route-specific data
            this.loadRouteData(route);

            // Update URL
            if (updateHistory) {
                window.history.pushState({ route }, '', `#${route}`);
            }
        }
    }

    loadRouteData(route) {
        switch (route) {
            case 'lists':
                this.loadUserLists();
                break;
            case 'search':
                this.loadUserListsForSearch();
                break;
            case 'profile':
                // Profile data is already loaded in PHP
                break;
            case 'user-profile':
                // User profile data is loaded when viewUserProfile is called
                break;
        }
    }

    async loadInitialData() {
        console.log('loadInitialData() called - UPDATED VERSION');
        try {
            await this.loadUserLists();
            // Also load followed users data for the profiles section
            await this.loadFollowedUsers();
            } catch (error) {
            console.error('Error loading initial data:', error);
        }
    }

    async loadUserLists() {
        console.log('loadUserLists() called');
        try {
            console.log('Fetching api/lists.php...');
            const response = await fetch('api/lists.php');
            console.log('Response received:', response.status, response.statusText);
            
            const data = await response.json();
            console.log('Data received:', data);
            
            if (data.success) {
                console.log('Success! Lists:', data.lists);
                console.log('Followed lists:', data.followed_lists);
                this.userLists = data.lists;
                this.renderUserLists(data.lists);
                this.renderFollowedLists(data.followed_lists || []);
            } else {
                console.error('API returned error:', data.message);
                this.showError(data.message || 'Failed to load lists');
            }
        } catch (error) {
            console.error('Error loading user lists:', error);
            this.showError('Failed to load lists');
        }
    }

    async loadUserListsForSearch() {
        try {
            const response = await fetch('api/lists.php');
            const data = await response.json();
            
            if (data.success) {
                this.userLists = data.lists;
            } else {
                this.showError(data.message || 'Failed to load lists');
            }
        } catch (error) {
            console.error('Error loading user lists:', error);
            this.showError('Failed to load lists');
        }
    }

    async loadFollowedUsers() {
        console.log('loadFollowedUsers() called');
        try {
            console.log('Fetching api/get_followed_users.php...');
            const response = await fetch('api/get_followed_users.php');
            console.log('Followed users response:', response.status, response.statusText);
            
            const data = await response.json();
            console.log('Followed users data:', data);
            
            if (data.success) {
                console.log('Success! Followed users:', data.followed_users);
                this.renderFollowedUsers(data.followed_users || []);
            } else {
                console.error('Failed to load followed users:', data.message);
            }
        } catch (error) {
            console.error('Error loading followed users:', error);
        }
    }

    renderUserLists(lists) {
        const container = document.getElementById('user-lists');
        if (lists.length === 0) {
            container.innerHTML = '<p class="muted">You haven\'t created any lists yet. Create your first list above!</p>';
            return;
        }

        container.innerHTML = lists.map(list => `
            <div class="list-card">
                <h3 class="list-name">
                    <a href="#" data-action="view-list" data-list-id="${list.id}">
                        ${this.escapeHtml(list.list_name)}
                    </a>
                </h3>
                <p class="list-meta">
                    ${list.item_count} items • 
                    ${list.is_public ? 'Public' : 'Private'} • 
                    Updated ${this.formatDate(list.updated_at)}
                </p>
                ${list.description ? `<p style="color: var(--muted-text); font-size: 0.9rem;">${this.escapeHtml(list.description)}</p>` : ''}
                <div class="list-actions">
                    <a href="#" data-action="view-list" data-list-id="${list.id}" class="btn btn-primary">View</a>
                    <a href="#" data-action="edit-list" data-list-id="${list.id}" class="btn btn-secondary">Edit</a>
                    <a href="#" data-action="delete-list" data-list-id="${list.id}" class="btn btn-danger">Delete</a>
                </div>
            </div>
        `).join('');
    }

    renderFollowedUsers(users) {
        const container = document.getElementById('followed-users');
        if (!container) {
            console.log('followed-users container not found, skipping render');
            return;
        }
        
        if (users.length === 0) {
            container.innerHTML = '<p class="muted">You\'re not following any users yet.</p>';
            return;
        }

        container.innerHTML = users.map(user => `
            <div class="list-card">
                <h3 class="list-name">${this.escapeHtml(user.first_name)} ${this.escapeHtml(user.last_name)}</h3>
                <p class="muted">@${this.escapeHtml(user.username)}</p>
                <p class="muted">${user.public_lists_count} public lists</p>
                <div class="list-actions">
                    <button class="btn btn-primary" data-action="view-user-profile" data-user-id="${user.id}">
                        View Profile
                    </button>
                    <button class="btn btn-secondary" data-action="unfollow-user" data-user-id="${user.id}">
                        Unfollow
                    </button>
                </div>
            </div>
        `).join('');
    }

    renderFollowedLists(lists) {
        const container = document.getElementById('followed-lists');
        if (lists.length === 0) {
            container.innerHTML = '<p class="muted">You\'re not following any users yet.</p>';
            return;
        }

        container.innerHTML = lists.map(list => `
            <div class="list-card">
                <h3 class="list-name">
                    <a href="#" data-action="view-list" data-list-id="${list.id}">
                        ${this.escapeHtml(list.list_name)}
                    </a>
                </h3>
                <p class="list-meta">
                    by ${this.escapeHtml(list.username)} • 
                    ${list.item_count} items • 
                    Updated ${this.formatDate(list.updated_at)}
                </p>
                ${list.description ? `<p style="color: var(--muted-text); font-size: 0.9rem;">${this.escapeHtml(list.description)}</p>` : ''}
                <div class="list-actions">
                    <a href="#" data-action="view-list" data-list-id="${list.id}" class="btn btn-primary">View</a>
                </div>
            </div>
        `).join('');
    }

    async createList() {
        const form = document.getElementById('create-list-form');
        const formData = new FormData(form);
        
        // Convert FormData to JSON
        const data = {
            list_name: formData.get('list_name'),
            description: formData.get('description'),
            is_public: formData.get('is_public') === '1'
        };
        
        try {
            const response = await fetch('api/create_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const responseData = await response.json();
            
            if (responseData.success) {
                this.showSuccess('List created successfully!');
                form.reset();
                await this.loadUserLists();
            } else {
                this.showError(responseData.message || 'Failed to create list');
            }
        } catch (error) {
            console.error('Error creating list:', error);
            this.showError('Failed to create list');
        }
    }

    async editList(listId) {
        console.log('editList method called with ID:', listId);
        
        if (!listId) {
            this.showError('Invalid list ID');
            return;
        }

        // Get the current list data
        try {
            console.log('Fetching list data for ID:', listId);
            const response = await fetch(`api/get_list.php?id=${listId}`);
            const data = await response.json();
            
            if (data.success) {
                const list = data.list;
                console.log('List data received:', list);
                
                // Show edit form
                const editModalId = 'edit-modal-' + listId;
                const editForm = `
                    <div class="modal" id="${editModalId}">
                        <div class="modal-content">
                            <h2>Edit List</h2>
                            <form id="edit-list-form-${listId}">
                                <div>
                                    <label for="edit-list-name-${listId}">List Name</label>
                                    <input type="text" id="edit-list-name-${listId}" name="list_name" value="${this.escapeHtml(list.list_name)}" required>
                                </div>
                                <div>
                                    <label for="edit-description-${listId}">Description</label>
                                    <textarea id="edit-description-${listId}" name="description">${this.escapeHtml(list.description || '')}</textarea>
                                </div>
                                <div>
                                    <label>
                                        <input type="checkbox" name="is_public" value="1" ${list.is_public ? 'checked' : ''}>
                                        Public List
                                    </label>
                                </div>
                                <div>
                                    <button type="submit">Update List</button>
                                    <button type="button" onclick="document.getElementById('${editModalId}').style.display='none'">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                console.log('Edit form HTML:', editForm);
                
                document.body.insertAdjacentHTML('beforeend', editForm);
                console.log('Edit form added to DOM');
                
                // Show the modal
                const modal = document.getElementById(editModalId);
                if (modal) {
                    modal.style.display = 'block';
                    console.log('Edit modal should now be visible');
                    
                    // Check if modal content exists
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        console.log('Edit modal content found:', modalContent.innerHTML.substring(0, 100) + '...');
                    } else {
                        console.error('Edit modal content not found');
                    }
                } else {
                    console.error('Edit modal not found after adding to DOM');
                }
                
                // Handle form submission
                document.getElementById('edit-list-form-' + listId).addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await this.updateList(listId);
                });
                
            } else {
                this.showError(data.message || 'Failed to load list');
            }
        } catch (error) {
            console.error('Error loading list for editing:', error);
            this.showError('Failed to load list');
        }
    }

    async updateList(listId) {
        const form = document.getElementById('edit-list-form-' + listId);
        const formData = new FormData(form);
        
        const data = {
            list_name: formData.get('list_name'),
            description: formData.get('description'),
            is_public: formData.get('is_public') === '1'
        };
        
        try {
            const response = await fetch(`api/update_list.php?id=${listId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const responseData = await response.json();
            
            if (responseData.success) {
                this.showSuccess('List updated successfully!');
                // Close the edit modal
                const editModal = document.getElementById('edit-modal-' + listId);
                if (editModal) {
                    editModal.style.display = 'none';
                    editModal.remove(); // Remove from DOM
                }
                await this.loadUserLists();
            } else {
                this.showError(responseData.message || 'Failed to update list');
            }
        } catch (error) {
            console.error('Error updating list:', error);
            this.showError('Failed to update list');
        }
    }

    async deleteList(listId) {
        if (!listId) {
            this.showError('Invalid list ID');
            return;
        }

        if (!confirm('Are you sure you want to delete this list? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`api/delete_list.php?id=${listId}`, {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('List deleted successfully!');
                await this.loadUserLists();
            } else {
                this.showError(data.message || 'Failed to delete list');
            }
        } catch (error) {
            console.error('Error deleting list:', error);
            this.showError('Failed to delete list');
        }
    }

    async viewList(listId) {
        if (!listId) {
            this.showError('Invalid list ID');
            return;
        }

        this.showLoading();
        
        try {
            const response = await fetch(`api/get_list.php?id=${listId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderListView(data.list, data.videos);
                this.navigateTo(`list/${listId}`);
            } else {
                this.showError(data.message || 'Failed to load list');
            }
        } catch (error) {
            console.error('Error loading list:', error);
            this.showError('Failed to load list');
        } finally {
            this.hideLoading();
        }
    }

    async playVideo(videoId, listId) {
        if (!videoId) {
            this.showError('Invalid video ID');
            return;
        }

        this.showLoading();
        
        try {
            const response = await fetch(`api/get_video.php?id=${videoId}&list_id=${listId || ''}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderVideoPlayer(data.video, data.playlist || []);
                this.navigateTo(`video/${videoId}`);
            } else {
                this.showError(data.message || 'Failed to load video');
            }
        } catch (error) {
            console.error('Error loading video:', error);
            this.showError('Failed to load video');
        } finally {
            this.hideLoading();
        }
    }

    async addToPlaylist(videoId, listId) {
        if (!videoId || !listId) {
            this.showError('Invalid video or list ID');
            return;
        }

        try {
            const response = await fetch('api/add_to_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    video_id: videoId,
                    list_id: listId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Video added to list successfully!');
            } else {
                this.showError(data.message || 'Failed to add video to list');
            }
        } catch (error) {
            console.error('Error adding video to list:', error);
            this.showError('Failed to add video to list');
        }
    }

    async removeFromPlaylist(videoId, listId) {
        if (!videoId || !listId) {
            this.showError('Invalid video or list ID');
            return;
        }

        if (!confirm('Are you sure you want to remove this video from the list?')) {
            return;
        }

        try {
            const response = await fetch('api/remove_from_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_id: videoId,
                    list_id: listId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Video removed from list successfully!');
                // Refresh the current list view
                const currentRoute = window.location.hash.substring(1);
                if (currentRoute.startsWith('list/')) {
                    const listId = currentRoute.split('/')[1];
                    this.viewList(listId);
                }
            } else {
                this.showError(data.message || 'Failed to remove video from list');
            }
        } catch (error) {
            console.error('Error removing video from list:', error);
            this.showError('Failed to remove video from list');
        }
    }

    showAddContentForm(listId) {
        console.log('showAddContentForm called with list ID:', listId);
        
        // Navigate to search page with the list ID
        this.navigateTo('search');
        
        // Store the target list ID for when user adds content
        this.targetListId = listId;
        
        // Show a message to the user
        this.showMessage('Search for videos to add to your list. Click "Add to List" on any video.', 'info');
    }

    async searchVideos() {
        const queryInput = document.getElementById('search-query');
        if (!queryInput) {
            console.error('Search query input not found');
            return;
        }
        
        const query = queryInput.value.trim();
        
        if (!query) {
            this.showError('Please enter a search query');
            return;
        }
        
        try {
            // Try real YouTube search first
            const response = await fetch('api/search_videos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: query })
            });
            const data = await response.json();
            
            if (data.success) {
                this.renderSearchResults(data.videos);
            } else {
                console.log('YouTube search failed, trying mock search:', data.message);
                // Fall back to mock search if YouTube credentials not available
                const mockResponse = await fetch('api/search_videos_mock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ query: query })
                });
                const mockData = await mockResponse.json();
                
                if (mockData.success) {
                    this.renderSearchResults(mockData.videos);
                    this.showError('Using demo videos (YouTube not connected)');
                } else {
                    this.showError('Failed to search videos');
                }
            }
        } catch (error) {
            console.error('Error searching videos:', error);
            this.showError('Failed to search videos');
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
            console.error('Profile search results container not found');
            return;
        }
        
        if (profiles.length === 0) {
            container.innerHTML = '<p class="muted">No users found. Try different search terms.</p>';
            return;
        }

        container.innerHTML = profiles.map(profile => `
            <div class="list-card">
                <h3 class="list-name">${this.escapeHtml(profile.first_name)} ${this.escapeHtml(profile.last_name)}</h3>
                <p class="muted">@${this.escapeHtml(profile.username)}</p>
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

    renderSearchResults(videos) {
        const container = document.getElementById('search-results');
        
        if (videos.length === 0) {
            container.innerHTML = '<p class="muted">No videos found. Try different search terms.</p>';
            return;
        }

        container.innerHTML = videos.map(video => `
            <div class="video-card">
                <img src="${this.escapeHtml(video.thumbnail_url)}" alt="Video thumbnail" class="video-thumbnail">
                <div class="video-info">
                    <h3 class="video-title">${this.escapeHtml(video.title)}</h3>
                    <p class="video-channel">by ${this.escapeHtml(video.channel_title)}</p>
                    <p style="color: var(--muted-text); font-size: 0.9rem; margin: 8px 0;">
                        Published ${this.formatDate(video.published_at)}
                    </p>
                    <div class="video-actions">
                        <select id="list-select-${video.video_id}" style="padding: 4px 8px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); border-radius: 4px; margin-right: 8px;">
                            <option value="">Select a list...</option>
                            ${this.userLists.map(list => `
                                <option value="${list.id}">${this.escapeHtml(list.list_name)}</option>
                            `).join('')}
                        </select>
                        <button class="btn btn-primary" data-action="add-to-list" data-video-id="${video.video_id}">Add to List</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async addVideoToList(videoId, listId) {
        if (!listId) {
            // Use stored target list ID if available (from "Add Content" button)
            if (this.targetListId) {
                listId = this.targetListId;
            } else {
                // Get list ID from select dropdown
                const select = document.getElementById(`list-select-${videoId}`);
                listId = select.value;
                
                if (!listId) {
                    this.showError('Please select a list');
                    return;
                }
            }
        }

        try {
            const response = await fetch('api/add_to_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ video_id: videoId, list_id: listId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Video added to list successfully!');
            } else {
                this.showError(data.message || 'Failed to add video to list');
            }
        } catch (error) {
            console.error('Error adding video to list:', error);
            this.showError('Failed to add video to list');
        }
    }

    async viewList(listId) {
        this.currentListId = listId;
        this.navigateTo('list-view');
        
        // Update URL to show the list ID
        window.history.pushState({ route: 'list-view', listId: listId }, '', `#list/${listId}`);
        
        try {
            const response = await fetch(`api/get_list.php?id=${listId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderListView(data.list, data.videos);
            } else {
                this.showError(data.message || 'Failed to load list');
            }
        } catch (error) {
            console.error('Error loading list:', error);
            this.showError('Failed to load list');
        }
    }

    renderListView(list, videos) {
        document.getElementById('list-view-title').textContent = list.list_name;
        document.getElementById('list-view-meta').textContent = 
            `by ${list.username} • ${videos.length} items • ${list.is_public ? 'Public' : 'Private'}`;
        
        const descriptionDiv = document.getElementById('list-description');
        const descriptionText = document.getElementById('list-description-text');
        
        if (list.description) {
            descriptionText.textContent = list.description;
            descriptionDiv.style.display = 'block';
        } else {
            descriptionDiv.style.display = 'none';
        }

        // Show/hide action buttons based on ownership
        const editBtn = document.getElementById('edit-list-btn');
        const addContentBtn = document.getElementById('add-content-btn');
        
        if (list.is_owner) {
            editBtn.style.display = 'inline-block';
            addContentBtn.style.display = 'inline-block';
            
            // Add the correct attributes for event listeners
            editBtn.setAttribute('data-action', 'edit-list');
            editBtn.setAttribute('data-list-id', list.id);
            addContentBtn.setAttribute('data-action', 'add-content');
            addContentBtn.setAttribute('data-list-id', list.id);
        } else {
            editBtn.style.display = 'none';
            addContentBtn.style.display = 'none';
        }

        // Render videos
        const container = document.getElementById('list-videos');
        
        if (videos.length === 0) {
            container.innerHTML = '<p class="muted">This list is empty.</p>';
            return;
        }

        container.innerHTML = videos.map((video, index) => `
            <div class="video-card">
                <img src="${this.escapeHtml(video.thumbnail_url)}" alt="Video thumbnail" class="video-thumbnail">
                <div class="video-info">
                    <h3 class="video-title">
                        <a href="#" data-action="play-video" data-video-id="${video.id}" data-list-id="${list.id}">
                            ${this.escapeHtml(video.title)}
                        </a>
                    </h3>
                    <p class="video-channel">by ${this.escapeHtml(video.channel_title)}</p>
                    <p style="color: var(--muted-text); font-size: 0.9rem; margin: 8px 0;">
                        Added ${this.formatDate(video.added_to_list_at)}
                    </p>
                    <div class="video-actions">
                        <a href="#" data-action="play-video" data-video-id="${video.id}" data-list-id="${list.id}" class="btn btn-primary">Play</a>
                        ${list.is_owner ? `<a href="#" data-action="remove-from-list" data-item-id="${video.id}" data-list-id="${list.id}" class="btn btn-danger">Remove</a>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    async playVideo(videoId, listId) {
        this.currentVideoId = videoId;
        this.currentListId = listId;
        this.navigateTo('video-player');
        
        try {
            const response = await fetch(`api/get_video.php?id=${videoId}&list_id=${listId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderVideoPlayer(data.video, data.playlist);
            } else {
                this.showError(data.message || 'Failed to load video');
            }
        } catch (error) {
            console.error('Error loading video:', error);
            this.showError('Failed to load video');
        }
    }

    renderVideoPlayer(video, playlist) {
        // Update video info
        document.getElementById('video-title').textContent = video.title;
        document.getElementById('video-meta').textContent = 
            `by ${video.channel_title} • Published ${this.formatDate(video.published_at)}`;
        
        const descriptionDiv = document.getElementById('video-description');
        if (video.description) {
            descriptionDiv.innerHTML = `<p style="white-space: pre-wrap; line-height: 1.6;">${this.escapeHtml(video.description)}</p>`;
        } else {
            descriptionDiv.innerHTML = '';
        }

        // Load YouTube player
        const playerContainer = document.getElementById('video-player-container');
        playerContainer.innerHTML = `
            <iframe 
                src="https://www.youtube.com/embed/${video.youtube_video_id}?autoplay=1&rel=0" 
                allowfullscreen
                allow="autoplay; encrypted-media">
            </iframe>
        `;

        // Render playlist
        this.renderPlaylist(playlist, video.id);
    }

    renderPlaylist(playlist, currentVideoId) {
        const container = document.getElementById('playlist-sidebar');
        
        if (playlist.length === 0) {
            container.innerHTML = '<p class="muted">No playlist available.</p>';
            return;
        }

        this.playlistVideos = playlist;
        this.currentVideoIndex = playlist.findIndex(video => video.id == currentVideoId);

        document.getElementById('playlist-title').textContent = 
            `Playlist (${this.currentVideoIndex + 1} of ${playlist.length})`;

        container.innerHTML = playlist.map((video, index) => `
            <div class="playlist-item ${index === this.currentVideoIndex ? 'current' : ''}">
                <img src="${this.escapeHtml(video.thumbnail_url)}" alt="Video thumbnail" class="playlist-thumbnail">
                <div class="playlist-info">
                    <h4 class="playlist-title">
                        <a href="#" data-action="play-video" data-video-id="${video.id}" data-list-id="${this.currentListId}">
                            ${this.escapeHtml(video.title)}
                        </a>
                    </h4>
                    <p class="playlist-channel">${this.escapeHtml(video.channel_title)}</p>
                </div>
            </div>
        `).join('');
    }

    async deleteList(listId) {
        if (!confirm('Are you sure you want to delete this list? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('api/delete_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ list_id: listId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('List deleted successfully!');
                await this.loadUserLists();
            } else {
                this.showError(data.message || 'Failed to delete list');
            }
        } catch (error) {
            console.error('Error deleting list:', error);
            this.showError('Failed to delete list');
        }
    }

    async removeFromList(itemId, listId) {
        if (!confirm('Remove this video from the list?')) {
            return;
        }

        try {
            const response = await fetch('api/remove_from_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ item_id: itemId, list_id: listId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Video removed from list!');
                this.viewList(listId); // Refresh the list view
            } else {
                this.showError(data.message || 'Failed to remove video from list');
            }
        } catch (error) {
            console.error('Error removing video from list:', error);
            this.showError('Failed to remove video from list');
        }
    }

    async viewUserProfile(userId) {
        try {
            console.log('Loading user profile for ID:', userId);
            const response = await fetch(`api/get_user_public_lists.php?user_id=${userId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderUserProfile(data.user, data.lists, data.is_following);
                this.navigateTo('user-profile');
                
                // Update URL to show the user ID
                window.history.pushState({ route: 'user-profile', userId: userId }, '', `#user-profile/${userId}`);
            } else {
                this.showError(data.message || 'Failed to load user profile');
            }
        } catch (error) {
            console.error('Error loading user profile:', error);
            this.showError('Failed to load user profile');
        }
    }
    
    renderUserProfile(user, lists, isFollowing) {
        console.log('Rendering user profile:', user, lists, isFollowing);
        
        // Update user info
        const nameElement = document.getElementById('user-profile-name');
        const metaElement = document.getElementById('user-profile-meta');
        
        console.log('Name element found:', nameElement);
        console.log('Meta element found:', metaElement);
        
        if (nameElement) {
            nameElement.textContent = `${user.first_name} ${user.last_name}`;
            console.log('Updated name element with:', `${user.first_name} ${user.last_name}`);
        } else {
            console.error('Name element not found!');
        }
        
        if (metaElement) {
            metaElement.textContent = `@${user.username} • Joined ${this.formatDate(user.created_at)}`;
            console.log('Updated meta element with:', `@${user.username} • Joined ${this.formatDate(user.created_at)}`);
        } else {
            console.error('Meta element not found!');
        }
        
        // Update follow/unfollow buttons
        const followBtn = document.getElementById('follow-user-btn');
        const unfollowBtn = document.getElementById('unfollow-user-btn');
        
        if (followBtn && unfollowBtn) {
            if (isFollowing) {
                followBtn.style.display = 'none';
                unfollowBtn.style.display = 'inline-block';
                unfollowBtn.onclick = () => this.unfollowUser(user.id);
            } else {
                followBtn.style.display = 'inline-block';
                unfollowBtn.style.display = 'none';
                followBtn.onclick = () => this.followUser(user.id);
            }
        }
        
        // Render public lists
        const listsContainer = document.getElementById('user-public-lists');
        console.log('Lists container found:', listsContainer);
        console.log('Lists to render:', lists);
        
        if (listsContainer) {
            if (lists.length === 0) {
                listsContainer.innerHTML = '<p class="muted">This user has no public lists.</p>';
                console.log('Rendered: No public lists message');
            } else {
                const listsHTML = lists.map(list => `
                    <div class="list-card">
                        <h3>${this.escapeHtml(list.name)}</h3>
                        <p class="muted">${list.item_count} videos • ${this.formatDate(list.updated_at)}</p>
                        <p>${this.escapeHtml(list.description || 'No description')}</p>
                        <button class="btn btn-primary" data-action="view-list" data-list-id="${list.id}">
                            View List
                        </button>
                    </div>
                `).join('');
                
                listsContainer.innerHTML = listsHTML;
                console.log('Rendered lists HTML:', listsHTML);
            }
        } else {
            console.error('Lists container not found!');
        }
    }

    async followUser(userId) {
        try {
            console.log('Following user ID:', userId);
            const response = await fetch('api/follow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('User followed successfully!');
                // Refresh the user profile if we're viewing it
                if (this.currentRoute === 'user-profile') {
                    this.viewUserProfile(userId);
                }
                // Refresh search results if we're in the profiles section
                if (this.currentRoute === 'profiles') {
                    const queryInput = document.getElementById('profile-search-query');
                    if (queryInput && queryInput.value.trim()) {
                        this.searchProfiles();
                    }
                }
            } else {
                this.showError(data.message || 'Failed to follow user');
            }
        } catch (error) {
            console.error('Error following user:', error);
            this.showError('Failed to follow user');
        }
    }

    async unfollowUser(userId) {
        try {
            console.log('Unfollowing user ID:', userId);
            const response = await fetch('api/unfollow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('User unfollowed successfully!');
                // Refresh the user profile if we're viewing it
                if (this.currentRoute === 'user-profile') {
                    this.viewUserProfile(userId);
                }
                // Refresh search results if we're in the profiles section
                if (this.currentRoute === 'profiles') {
                    const queryInput = document.getElementById('profile-search-query');
                    if (queryInput && queryInput.value.trim()) {
                        this.searchProfiles();
                    }
                }
            } else {
                this.showError(data.message || 'Failed to unfollow user');
            }
        } catch (error) {
            console.error('Error unfollowing user:', error);
            this.showError('Failed to unfollow user');
        }
    }

    // Utility functions
    showLoading() {
        document.getElementById('loading').style.display = 'block';
    }

    hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    showError(message) {
        this.showMessage(message, 'error');
    }

    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    showMessage(message, type) {
        const messagesContainer = document.getElementById('messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = type;
        messageDiv.textContent = message;
        
        messagesContainer.appendChild(messageDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 5000);
    }

    closeModal() {
        document.getElementById('modal').style.display = 'none';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
}

// Initialize SPA when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new StreamingSPA();
});
