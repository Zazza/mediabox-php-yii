#
# Dispatches by rendering Jinja2 templates.
#
# The preferred media type is selected based on the template filename
# extension. E.g., ".html" to "text/html".
#
# The rendering context is the Prudence conversation.locals.
#
# An internal "POST" with a web form will forward the form
# to conversation.locals. This allows for MVC functionality.
#

from jinja2 import Environment, FileSystemLoader, FileSystemBytecodeCache
from jinja2.exceptions import TemplateNotFound
from os import sep, makedirs
from os.path import splitext
import urllib 
import gettext
import simplejson as json

# Load templates from the configured directory 
loader1 = application.globals.get('xvid.jinja.sharedTemplateDir')
loader2 = application.globals.get('xvid.jinja.appTemplateDir')
loader = FileSystemLoader([loader1, loader2])

# Byte-code cache in the "/cache/jinja2/{app}/" subdirectory of the container
# (Unfortunately this is buggy under Jython! We occassionally get "bad marshal data" errors)
#cache = application.containerRoot.path + sep + 'cache' + sep + 'jinja2' + sep + application.root.name
#try:
#    makedirs(cache)
#except:
#    pass
#cache = FileSystemBytecodeCache(cache, '%s.cache')
#env = Environment(loader=loader, bytecode_cache=cache)

env = Environment(loader=loader, extensions=['jinja2.ext.i18n'], trim_blocks=True)

def handle_init(conversation):
    # The preferred media type depends on the tempate's filename extension
    id = conversation.wildcard
    if id is not None:
    	filename, extension = splitext(id)
    	if extension is not None:
        	extension = extension[1:]
        	media_type = application.getMediaTypeByExtension(extension)
        	if media_type is not None:
            		conversation.addMediaType(media_type)
    conversation.addMediaTypeByName('text/plain')
    conversation.addMediaTypeByName('application/json')

def handle_post(conversation):
    # POST only supported by internal calls
    if not conversation.internal:
        return 405

    obj = json.loads(conversation.entity.text)

    id = conversation.wildcard

    
    try :
        locale = obj['locale']
        '''TODO: Need to find proper way of loading textpack according to the user's locale to request specific rather than setting textpack
        on environment '''
        trans = gettext.GNUTranslations(open( application.root.path + sep + "textpacks" + sep + locale + ".mo", "rb" ) )
        env.install_gettext_translations(trans)
    except Exception :
        env.install_null_translations()
    try:
        
        template = env.get_template(id)
        return template.render(**dict(obj))
    except TemplateNotFound:
        return None

def handle_get(conversation):
	return 404
