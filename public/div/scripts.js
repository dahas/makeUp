
$(document).ready(() => {

    let fadeDurMS = 200;

    setRoute = (mod, uri) => {
        $(this).blur();
        $('nav li a.active').removeClass('active');
        let state = { path: uri, html: '', title: '' };
        loadContent(state)
        history.pushState(state, mod, uri);
    }

    loadContent = async state => {
        if (!state) {
            let data = await requestData('?mod=index');
            $('#content').html(data.html);
            $(document).prop('title', data.title);
        } else if (state.html == '') {
            let data = await requestData(state.path);
            $('#content').html(data.html);
            $(document).prop('title', data.title);
        } else {
            $("#content").animate({ opacity: 0 }, fadeDurMS, () => {
                $('#content').html(state.html);
                $(document).prop('title', state.title);
                $("#content").animate({ opacity: 1 }, fadeDurMS);
            });
        }
    }

    requestData = async path => {
        $("#content").animate({ opacity: 0 }, fadeDurMS);
        let state = {};
        await $.ajax({
            type: 'GET',
            url: path + '&app=nowrap'
        }).fail(() => {
            state = {path: path, html: "Sorry! Something has gone wrong :(", title: "Page not found!"};
        }).done(data => {
            let json = jQuery.parseJSON(data);
            state = {path: path, html: json.html, title: json.title};
            history.replaceState(state, json.module, path);
            $("#content").animate({ opacity: 1 }, fadeDurMS);
        });
        return state;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }
});

