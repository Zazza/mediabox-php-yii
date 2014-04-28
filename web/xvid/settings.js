app.settings.description.name = 'Xvid Mediabox'
app.settings.description.description = 'Xvid Mediabox web frontend'

app.globals.mongoDb.applicationDb = {
	name 		: appDbName,
	username  : appDbUserName,
	password  : appDbPassword
}

app.globals.config = {
        session_limit: 3600,
        session_long_limit: 2592000,
        // option for tushkan or mblclient
        storage: 'http://tushkan.com/fm',
        is_nimbus_client: false
        //storage: 'http://localhost:10000',
        //is_nimbus_client: true
}

app.globals.mediaTypes = {
    image: '/assets/img/mediabox/ftypes/image.png',
    doc: '/assets/img/mediabox/ftypes/msword.png',
    pdf: '/assets/img/mediabox/ftypes/pdf.png',
    txt: '/assets/img/mediabox/ftypes/text.png',
    exe: '/assets/img/mediabox/ftypes/executable.png',
    xls: '/assets/img/mediabox/ftypes/excel.png',
    audio: '/assets/img/mediabox/ftypes/audio.png',
    html: '/assets/img/mediabox/ftypes/html.png',
    zip: '/assets/img/mediabox/ftypes/compress.png',
    video: '/assets/img/mediabox/ftypes/flash.png',
    any: '/assets/img/mediabox/ftypes/unknown.png',
    folder: '/assets/img/mediabox/ftypes/folder.png'
}

app.globals.extension = [
    ['image', 'bmp', 'jpg', 'jpeg', 'gif', 'png'],
    ['audio', 'ogg', 'mp3'],
    ['video', 'mp4', 'mov', 'wmv', 'flv', 'avi', 'mpg', '3gp'],
    ['text', 'txt'],
    ['doc', 'doc', 'rtf', 'docx'],
    ['pdf', 'pdf', 'djvu'],
    ['txt', 'txt', 'lst', 'ini'],
    ['exe', 'exe', 'com',' bat', 'sh'],
    ['xls', 'xls', 'xlsx'],
    ['html', 'htm', 'html', 'shtml'],
    ['zip', 'zip', 'rar', 'tar', 'gz', '7z']
]

    

