document.executeOnce('/sincerity/classes/')
document.executeOnce('/prudence/resources/')
document.executeOnce('/xvid/controllers/baseController/')
document.executeOnce('/xvid/util/navigation/')
document.executeOnce('/xvid/util/apiHelper/')
document.executeOnce('/mediabox-data/data-session/')

var Xvid = Xvid || {}
/**
 * My MediaCoder controller
 * 
 * @class
 * @augments Xvid.BaseController
 * @name Xvid.MyMediaCoderController
 */
Xvid.MediaBoxBaseController = Xvid.MediaBoxBaseController || Sincerity.Classes.define(function() {
    
    /** @exports Public as MediaBoxBaseController */
    var Public = {}
    /** @ignore */
    Public._inherit = Xvid.BaseController
    /**
     * The library's logger.
     * 
     * @field
     * @returns {Prudence.Logging.Logger}
     */
    Public.logger = Prudence.Logging.getLogger('MediaBoxBaseController')
    /**
     * @returns {HTML} requested html page
     */
     
     /**
     *
     * @param context
     */
   Public.appendMediaboxValues = function(context){
   	   try{
	       context.mediabox = context.mediabox || {}
	       var current_directory = session_get("current_directory")
	       var volume = session_get("volume")
	       context.mediabox['storage'] = application.globals.get('config.storage')
	       context.mediabox['is_nimbus_client'] = application.globals.get('config.is_nimbus_client')
	       var types = session_get('types')
	       if (types != "") {
	           types = types.split("&")
	           var type = Array()
	           for(part in types) {
	               type = types[part].split("=")
	               if (type[0] == "other")
	                   var check_type_other = type[1]
	               if (type[0] == "image")
	                   var check_type_image = type[1]
	               if (type[0] == "video")
	                   var check_type_video = type[1]
	               if (type[0] == "music")
	                   var check_type_music = type[1]
	           }
	       } else {
	           var check_type_other = true
	           var check_type_image = true
	           var check_type_video = true
	           var check_type_music = true
	       }
	       context.mediabox['check_type_other'] = check_type_other
	       context.mediabox['check_type_image'] = check_type_image
	       context.mediabox['check_type_video'] = check_type_video
	       context.mediabox['check_type_music'] = check_type_music
	       var view = session_get("view")
	       if (view == "") {
	           view = "grid"
	       }
	       context.mediabox['view'] = view
	       var sort = session_get("sort")
	       if (sort == "") {
	           sort = "name"
	       }
	       context.mediabox['sort'] = sort
	 
	       if (current_directory)
	           var startdir = current_directory
	       else
	           var startdir = 0
	       context.mediabox['startdir'] = startdir
	       if (volume)
	           var volume_level = volume
	       else
	           var volume_level = 50
	       context.mediabox['volume_level'] = volume_level
	 
	       var playlist = session_get("playlist")
	       if (playlist)
	           context.mediabox['playlist'] = playlist
       }catch(e){
       		Public.logger.info('>>>>>>>>>>>>> eror  ' + Sincerity.JSON.to(e))
       }
   }
   
    Public.getView = function(conversation, uri, context){
    	if(!context) {
            context = {}
        }
    	Public.appendMediaboxValues(context)
    	context.baseRef = conversation.request.hostRef+'/' //conversation.reference.baseRef
        return arguments.callee.overridden.call(this, conversation, uri, context)       
    }
    
    /**
     * Default Get handler - It returns the jinja page lies under 'template' folder 
     * based upon the requested URI 
     * @param conversation
     * @returns requested HTML page
     */
    Public.handleGet = function(conversation) {
    
        try {
            if (this.doGet) {
                return this.doGet(conversation)
            }else{
                return Public.getView(conversation)
            }
        } catch (x) {
            return returnError(conversation, x)
        }
    }
    
    var returnError = function(conversation , x){
        Public.logger.severe(Sincerity.JSON.to(x))
        return 500
    }

    return Public
}())
