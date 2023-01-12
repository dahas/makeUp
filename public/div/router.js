
$(document).ready(() => {

    let fadeDurMS = 200;

    setRoute = (obj, mod, uri) => {
        if (mod) {
            $(this).blur();
            if (obj) {
                $('nav.navbar a.active').removeClass('active');
                $(obj).addClass('active');
            }
            let state = { path: uri, caching: true, title: '', content: '' };
            loadContent(state)
            history.pushState(state, mod, uri);
        }
    }

    loadContent = state => {
        $('*[data-mod="App"]').animate({ opacity: 0 }, fadeDurMS, async () => {
            let data = await requestData(state.path);
            $('*[data-mod="App"]').html(data.content);
            $(document).prop('title', data.title);
            $('*[data-mod="App"]').animate({ opacity: 1 }, fadeDurMS);
        });
    }

    requestData = async route => {
        let state = {};
        await $.ajax({
            type: 'GET',
            url: route + '?json',
            dataType: 'json',
            headers: {
                Route: route
            }
        }).fail(() => {
            state = {
                path: route,
                title: "Error!",
                content: "Sorry! Something has gone wrong :("
            };
        }).done(data => {
            state = { path: route, caching: data.caching, title: data.title, content: data.content };
            history.replaceState(state, data.module, route);
            $('*[data-mod="App"]').html(data.content);
            $(document).trigger("module.loaded", [data.module]); 
        });
        return state;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }

});

