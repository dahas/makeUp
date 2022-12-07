
$(document).ready(() => {

    setRoute = (mod, uri) => {
        $(this).blur();
        $('nav li a.active').removeClass('active');
        let state = { path: uri, html: '' };
        loadContent(state)
        history.pushState(state, mod, uri);
    }

    loadContent = state => {
        if (!state) {
            requestData('?mod=index');
        } else if (state.html == '') {
            requestData(state.path);
        } else {
            $('#content').html(state.html);
        }
    }

    requestData = async path => {
        $("#content").animate({ opacity: 0 }, 200);
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
            $("#content").animate({ opacity: 1 }, 200);
        });
        $('#content').html(content);
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }
});

