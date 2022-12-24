
$(document).ready(() => 
{
    setLanguage = lang => {
        $.ajax({
            type: 'GET',
            url: '/Language?task=change&cc=' + lang,
            dataType: 'json'
        }).fail()
            .done(data => {
                console.log(data)
                if (data.result == 1) {
                    location.href = location.href;
                }
            });
    }
});

