
app.hosts = {
    'public-mediabox': '/',
    'public-services': appBaseURL
}
app.routes = {
    '/*': [
        {
            type: 'filter',
            library: '/xvid/util/protected/',
            next: 'manual'
        },
        
        {
            type: 'filter',
            next: 'templates',
            library: '/xvid/util/protected/'
        },
        {
            type: 'cacheControl',
            mediaTypes: {
                'image/png': '1h',
                'image/gif': '1h',
                'image/jpeg': '1h',
                'text/css': '1h'
                
            },
            next: {
                type: 'less',
                next: 'static'
            }
        },
        '@defaultController'
    ],
    '/oldindex/': {
        type: 'filter',
        library: '/protected/',
        next: '@mbHomeController'
    },
    '/upload_success/{id}': {
        type: 'filter',
        library: '/protected/',
        next: '@mbUploadSuccessController'
    },
    '/fragments/*' : '@jinja:error.html',
    '/fragments_shared/*' : '@jinja:error.html',
    '/jinja/*' : '/jinja/',
    '/error/' : '@jinja:error.html',
    '/internal_error/' : '@jinja:internal_error.html',
    '/mediabox/assets/*' : '/assets/{rr}',
    '/auth/{action}/': '@auth',
	'/app/{action}/': '@app',
    '/fm/{action}/': '@fm',
    '/thumb/{_id}/': '@thumb',
    '/image/{action}/': '@image',
    '/audio/{action}/': '@audio',
    '/trash/': {
        type: 'filter',
        library: '/protected/',
        next: '@trash'
    }
}
app.errors = {
    404 : '/error/!',
    500 : '/internal_error/!' 
}