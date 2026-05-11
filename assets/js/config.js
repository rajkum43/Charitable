(function () {

    const origin = window.location.origin;

    const pathParts = window.location.pathname
        .split('/')
        .filter(Boolean);

    let projectFolder = '';

    /*
    |--------------------------------------------------------------------------
    | Localhost Project Folder Detect
    |--------------------------------------------------------------------------
    */
    if (
        origin.includes('localhost') &&
        pathParts.length > 0 &&
        pathParts[0] !== 'member' &&
        pathParts[0] !== 'admin' &&
        pathParts[0] !== 'api'
    ) {
        projectFolder = pathParts[0];
    }

    /*
    |--------------------------------------------------------------------------
    | Build Base URL Properly
    |--------------------------------------------------------------------------
    */
    window.BASE_URL = projectFolder
        ? origin + '/' + projectFolder + '/'
        : origin + '/';

    /*
    |--------------------------------------------------------------------------
    | Other URLs
    |--------------------------------------------------------------------------
    */
    window.API_URL = window.BASE_URL + 'api/';
    window.ADMIN_PATH = window.BASE_URL + 'admin/';
    window.MEMBER_PATH = window.BASE_URL + 'member/';

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    */
    console.log('BASE_URL:', window.BASE_URL);
    console.log('API_URL:', window.API_URL);

})();