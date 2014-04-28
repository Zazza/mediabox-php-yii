document.require(
        '/sincerity/container/',
        '/prudence/setup/')

var app = new Prudence.Setup.Application()

document.execute('/xvid/applications/settings/')
try {
	Sincerity.Container.execute('settings')
} catch(x) {
    println(x)
    throw x

}

document.execute('/xvid/applications/routing/')
try {
	Sincerity.Container.execute('routing')
} catch(x) {
    println(x)
    throw x
}

app = app.create(component)

// Restlets
Sincerity.Container.executeAll('restlets')