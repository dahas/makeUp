
$(document).ready(() => {

    window.onpopstate = event => {
        loadContent(event.state);
    }

    loadContent = state => {
        let path = state ? state.path : '?mod=index&app=nowrap';

        $("#content").animate({ opacity: 0 }, 200);

        $.ajax({
            type: 'GET',
            url: path
        }).fail(() => {
            $('#content').html("Sorry! Something has gone wrong :(");
        }).done(data => {
            let json = jQuery.parseJSON(data);
            
            $(document).prop('title', json.title);
            $('#content').html(json.html);

            $("#content").animate({ opacity: 1 }, 200);
        });
    }

    setRoute = (mod, route) => {
        $(this).blur();
        $('nav li a.active').removeClass('active');

        let state = { path: route + '&app=nowrap' };

        loadContent(state)

        history.pushState(state, mod, route);
    }
});

