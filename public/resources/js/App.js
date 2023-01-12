
$(document).ready(() => {

    /*****************************************************************************\
    | Below we handle dispatched events from modules.                             |
    |                                                                             |
    | NOTE: Events must be registered on document level since HTML elements are   |
    | inserted and removed from the DOM asynchronously on demand.                 |
    \*****************************************************************************/

    // Events are triggered in Authentication.js
    $(document).on("user.auth", (event, context) => {
        loadModule('Navigation');
    });
    $(document).on("user.logged.in", (event, context) => {
        loadModule('Authentication', 'buildLogoutForm');
        setRoute(null, context);
    });
    $(document).on("user.logged.out", (event, context) => {
        loadModule('Authentication', 'buildLoginForm');
        setRoute(null, context);
    });
    $(document).on("user.registered", (event, context) => {
        loadModule('Authentication', 'buildLogoutForm');
        setRoute(null, context);
    });

    /******************************************************************************/

    loadModule = (module, task) => {
        let route = "/" + module;
        if (task) {
            route += "/" + task;
        }

        $.ajax({
            type: 'GET',
            url: route + '?json',
            dataType: 'json'
        }).done(data => {
            $('*[data-mod="' + module + '"]').html(data.content);
        });
    }

    showToast = (tid, msg) => {
        $('#toast-' + tid + ' span.toast-msg').html(msg);
        const toast = new bootstrap.Toast($('#toast-' + tid), { animation: true, delay: 3000 });
        toast.show();
    }

    const tempToast = JSON.parse(localStorage.getItem("toast"));
    if (tempToast) {
        showToast(tempToast[0], tempToast[1]);
        localStorage.removeItem("toast")
    }

    /*****************************************************************************\
    | ROUTING                                                                     |
    |                                                                             |
    | NOTE: A route is the Path segment of an URL.                                |
    | E.g.: http://www.domain.tld/PATH                                            |
    | In makeUp the first Path segment is the name of the Module while the        |
    | optional second segment is a specific task.                                 |
    | E.g.: http://www.domain.tld/Module/task                                     |
    | IMPORTANT: The first letter of a Module is always capitalized!              |
    \*****************************************************************************/

    let fadeDurMS = 200;

    setRoute = (obj, mod, task) => {
        let route = "/" + mod;
        if (task) {
            route += "/" + task;
        }

        if (mod) {
            $(this).blur();
            if (obj) {
                $('nav.navbar a.active').removeClass('active');
                $(obj).addClass('active');
            }
            let state = { path: route, caching: true, title: '', content: '' };
            loadContent(state)
            history.pushState(state, mod, route);
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

