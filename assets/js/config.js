/**
 * Global Configuration File
 * Sets up the BASE_URL for all API calls dynamically
 */

(function() {
    // Get current pathname
    const pathname = window.location.pathname;
    
    // Determine base URL by removing /admin/ or /member/ from pathname
    let basePath = pathname;
    
    // Remove /admin/ or /member/ and everything after to get base directory
    if (basePath.includes('/admin/')) {
        basePath = basePath.split('/admin/')[0] + '/';
    } else if (basePath.includes('/member/')) {
        basePath = basePath.split('/member/')[0] + '/';
    } else {
        // Fallback: get the first directory only
        const parts = basePath.split('/').filter(p => p);
        basePath = parts.length > 0 ? '/' + parts[0] + '/' : '/';
    }
    
    // Set base URLs dynamically
    window.BASE_URL = basePath;
    window.API_URL = window.BASE_URL + 'api/';
    window.ADMIN_PATH = window.BASE_URL + 'admin/';
    window.MEMBER_PATH = window.BASE_URL + 'member/';
    
    // Configuration loaded successfully
})();
