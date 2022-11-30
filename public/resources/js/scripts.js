
$(document).ready(() => {

    // Asynchronous return of the content of a module
    loadContent = qs => {
        $(this).blur();
        $('nav li a.active').removeClass('active');
        
        $("#content").animate({opacity:0}, 500);

        $.ajax({
            type: 'GET',
            url: 'index.php' + qs + '&app=nowrap'
        })
        .fail(() => {
            $('#content').html("Sorry! Something has gone wrong :(");
        })
        .done(data => {
            $('#content').html(data);
            $("#content").animate({opacity:1}, 500);
        });
    }
});

