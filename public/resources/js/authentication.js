
$(document).ready(() => {

    auth = (formName, path) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="'+formName+'"]').serialize(),
            success: data => {
                console.log(data);
                data.segments.forEach(segment => {
                    $('*[data-mod="' + segment.dataMod + '"]').html(segment.html);
                })
                if (data?.toast) {
                    showToast(data.toast[0], data.toast[1]);
                }
            },
            dataType: 'json'
        });
    }

});

