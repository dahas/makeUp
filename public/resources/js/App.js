
$(document).ready(() => {

    // $(document).on("user.auth", event => {
    //     loadModule('Navigation');
    //     loadModule('Authentication');
    // });

    loadModule = modName => {
        $.ajax({
            type: 'GET',
            url: modName + '?json',
            dataType: 'json'
        }).done(data => {
            $('*[data-mod="'+modName+'"]').html(data.content);
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

