document.executeOnce('/sincerity/classes/')
document.executeOnce('/prudence/resources/')
document.executeOnce('/xvid/controllers/baseController/')


function handleInit(conversation){
    conversation.addMediaTypeByName('text/html')
    conversation.addMediaTypeByName('text/plain')
}

function handleGet(conversation){
    var baseController = new Xvid.BaseController()
    var id = conversation.locals.get('com.threecrickets.prudence.dispatcher.id')
    return baseController.getView(conversation, id)
}
