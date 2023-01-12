
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

    // submitForm = (path, name, reload) => {
    //     $.ajax({
    //         type: 'POST',
    //         url: path,
    //         data: $('form[name="' + name + '"]').serialize(),
    //         success: data => {
    //             $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
    //             if (reload) {
    //                 if (data.payload?.toast) {
    //                     localStorage.setItem("toast", JSON.stringify(data.payload.toast));
    //                 }
    //                 location.reload();
    //             } else {
    //                 if (data.payload?.toast) {
    //                     showToast(data.payload.toast[0], data.payload.toast[1]);
    //                 }
    //                 if (data.content) {
    //                     $('*[data-mod="App"]').html(data.content);
    //                 }
    //             }
    //         },
    //         dataType: 'json'
    //     });
    // }

});

