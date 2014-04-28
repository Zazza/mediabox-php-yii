document.executeOnce('/sincerity/classes/')
document.executeOnce('/prudence/resources/')
document.executeOnce('/controllers/mediaBoxBaseController/')
document.executeOnce('/xvid/util/navigation/')
document.executeOnce('/xvid/util/apiHelper/')
document.executeOnce('/sincerity/json/')
document.executeOnce('/xvid/util/db/')
document.executeOnce('/util/collectionNames/')

var Xvid = Xvid || {}

//Xvid.MediaBoxController = Xvid.MediaBoxController || Sincerity.Classes.define(function()
Xvid.MediaBoxController = Xvid.MediaBoxController || function() {
    
    var mbfileCollectionName = 'mbfiles'
    var mbfileCollection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.mbfiles)
    var mbUploadStatsCollectionName = 'mbuploadstats'
    var mbUploadStatsCollection = Util.db.getAppCollection(Xvid.CollectionNames.MBWeb.mbuploadstats)
    
    
    var Public = {}
    
    Public.logger = Prudence.Logging.getLogger('mediaBoxController')
    
    Public.Home = Sincerity.Classes.define(function(Module) {
    	var Public = {}
    	
        Public._inherit = Xvid.MediaBoxBaseController
        
        Public.doGet = function(conversation) {
        	var session = Public.getSession(conversation)
        	var cookie = conversation.getCookie('xvid.session')
        	var access_token = Sincerity.JSON.from(decodeURIComponent(cookie.value)).key
        	
            var user_id = session.data.username
        	var mbfiles = []
            var cursor = mbfileCollection.find({'user_id' : user_id }).sort({'$natural': -1})
            try {
                while (cursor.hasNext()) {
                    mbfiles.push(cursor.next())
                }
            } finally {
                cursor.close()
            }
            var uri = 'oldindex.html'
            return this.getView(conversation, uri, {files : mbfiles, 
            	mbclienturl: 'http://localhost:10000', 
            	access_token: encodeURIComponent(access_token)})
        }
        
        /* TODO: REMOVE THIS FUNC and use getSession from BaseController */
        Public.getSession = function(conversation){
            var session = null
            var cookie = conversation.getCookie('xvid.session')
            if (null !== cookie) {
                cookie = Sincerity.JSON.from(decodeURIComponent(cookie.value))
                session = Xvid.Authorization.getSessionByKey(cookie.key)
                if (null !== session) {
                    if (!session.isAuthorized()) {
                        session = null
                    }
                    else {
                        session.update()
                    }
                }
            }
            return session
        }
        return Public
    }(Public))
    
    Public.UploadSuccess = Sincerity.Classes.define(function(Module) {
    	var Public = {}
        Public._inherit = Xvid.MediaBoxBaseController
        
        Public.doGet = function(conversation) {
            var fileId = conversation.locals.get('id')
            var mbuploadstats = mbUploadStatsCollection.findOne({ 'file_id' : fileId })
            var uri = '/upload_success.html'
            return this.getView(conversation, uri, mbuploadstats)
        }
        return Public
    }(Public))

    return Public

}()
