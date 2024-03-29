
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
        setRoute(context);
    });
    $(document).on("user.logged.out", (event, context) => {
        loadModule('Authentication', 'buildLoginForm');
        setRoute(context);
    });
    $(document).on("user.registered", (event, context) => {
        loadModule('Authentication', 'buildLogoutForm');
        setRoute(context);
    });

    /******************************************************************************/

    /**
     * Use this function to load content into a segment of the page.
     */
    loadModule = (module, task) => {
        let route = "/" + module;
        if (task) {
            route += "/" + task;
        }

        $.ajax({
            type: 'GET',
            url: route,
            dataType: 'json'
        }).done(data => {
            $('*[data-mod="' + module + '"]').html(data.content);
        });
    }

    /**
     * Use this function to display a toast.
     * @param alert String - "success", "warning" or "error"
     * @param msg String - The message to display
     */
    showToast = (alert, msg) => {
        $('#toast-' + alert + ' span.toast-msg').html(msg);
        const toast = new bootstrap.Toast($('#toast-' + alert), { animation: true, delay: 3000 });
        toast.show();
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
    |                                                                             |
    | USAGE: Use "setRoute(null, 'ModName'[, 'taskName'])" to redirect users or   |
    | to display a page.                                                          |
    \*****************************************************************************/

    let fadeDurMS = 200;

    /**
     * Use this function to display a page.
     */
    setRoute = (mod, task, element) => {
        mod = typeof mod == 'undefined' ? '' : mod;
        task = typeof task == 'undefined' ? '' : task;
        element = typeof element == 'undefined' ? null : element;

        let route = "/" + mod;
        if (task) {
            route += "/" + task;
        }

        $(this).blur();
        if (element) {
            $('nav.navbar a.active').removeClass('active');
            $(element).addClass('active');
        }
        let state = { path: route, caching: true, title: '', content: '' };
        loadContent(state)
        history.pushState(state, mod, route);
    }

    loadContent = state => {
        let path = state ? state.path : "/"
        $('*[data-mod="App"]').animate({ opacity: 0 }, fadeDurMS, async () => {
            let data = await requestData(path)
                .catch(() => { console.log("ERROR! Cannot render Module! Please check your controller.") });
            if (data) {
                $('*[data-mod="App"]').html(data.content);
                $(document).prop('title', data.title);
                $('*[data-mod="App"]').animate({ opacity: 1 }, fadeDurMS);
            }
        });
    }

    requestData = async route => {
        let state = {};
        await $.ajax({
            type: 'GET',
            url: route,
            dataType: 'json',
            headers: {
                "X-makeUp-Route": route,
                "X-makeUp-Ajax": 1
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
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
