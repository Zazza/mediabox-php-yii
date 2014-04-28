document.executeOnce('/controllers/mediaBoxBaseController/')
document.executeOnce('/controllers/mediaBoxController/')
document.executeOnce('/controllers/mediabox/app/')
document.executeOnce('/controllers/mediabox/fm/')
document.executeOnce('/controllers/mediabox/thumb/')
document.executeOnce('/controllers/mediabox/image/')
document.executeOnce('/controllers/mediabox/audio/')
document.executeOnce('/controllers/mediabox/trash/')

resources = {
    defaultController : new Xvid.MediaBoxBaseController(), 
    mbHomeController : new Xvid.MediaBoxController.Home(),
    mbUploadSuccessController : new Xvid.MediaBoxController.UploadSuccess(),
    app: new AppResource(),
    fm: new FmResource(),
    thumb: new ThumbResource(),
    image: new ImageResource(),
    audio: new AudioResource(),
    trash: new Xvid.MediaBoxTrashController.Home()
}

