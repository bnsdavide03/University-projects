jQuery(document).ready(function ($) {
    $('#go-back-button').on('click', () => {
        window.history.back();
    });

    $('.redirect-button').on('click', (e) => {
        window.location = $(e.currentTarget).data('url');
    });

    $('#search').on('input', (e) => {
        const query = $(e.currentTarget).val();

        $.get('index.php?search=' + query, function (data) {
            var parser = new DOMParser();
            var htmlDoc = parser.parseFromString(data, 'text/html');
            const vehicles = htmlDoc.getElementById('vehicles');

            $("#vehicles").html(vehicles);
        });
    }); 
});
