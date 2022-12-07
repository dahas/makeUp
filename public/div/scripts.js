
$(document).ready(() => {

    let fadeDurMS = 200;

    setRoute = (mod, uri) => {
        $(this).blur();
        $('nav li a.active').removeClass('active');
        let state = { path: uri, html: '' };
        loadContent(state)
        history.pushState(state, mod, uri);
    }

    loadContent = async state => {
        if (!state) {
            let content = await requestData('?mod=index');
            $('#content').html(content);
        } else if (state.html == '') {
            let content = await requestData(state.path);
            $('#content').html(content);
        } else {
            $("#content").animate({ opacity: 0 }, fadeDurMS, () => {
                $('#content').html(state.html);
                $("#content").animate({ opacity: 1 }, fadeDurMS);
            });
        }
    }

    requestData = async path => {
        $("#content").animate({ opacity: 0 }, fadeDurMS);
        let content = '';
        await $.ajax({
            type: 'GET',
            url: path + '&app=nowrap'
        }).fail(() => {
            content = "Sorry! Something has gone wrong :(";
        }).done(data => {
            let json = jQuery.parseJSON(data);
            $(document).prop('title', json.title);
            content = json.html;
            history.replaceState({path: path, html: json.html}, json.module, path);
            $("#content").animate({ opacity: 1 }, fadeDurMS);
        });
        return content;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }
});

