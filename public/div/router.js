
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

    requestData = async path => {
        let state = {};
        await $.ajax({
            type: 'GET',
            url: path + '?json',
            dataType: 'json'
        }).fail(() => {
            state = {
                path: path,
                title: "Error!",
                content: "Sorry! Something has gone wrong :("
            };
        }).done(data => {
            state = { path: path, caching: data.caching, title: data.title, content: data.content };
            history.replaceState(state, data.module, path);
            $('*[data-mod="App"]').html(data.content);
        });
        return state;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }

});

