$(document).ready(function () {
    $(document).on('change', '#hotels', {}, function (event) {
        loadOwnerHotelUsers(event, $('select option:selected'));
    });
});

function loadOwnerHotelUsers(event, element) {
    $.ajax({
        url: $(element).attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {
            'type': $(element).attr('data-role'),
            'pageNumber': $(element).attr('data-page'),
            'hotelId': $(element).val()
        },
        async: true,

        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
