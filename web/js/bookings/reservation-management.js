$(document).ready(function () {
    $(document).on('click', '.delete-reservation', {}, function (event) {
        deleteReservation(event, this);
    });
});

function deleteReservation(event, element)
{
    console.log(element);
    event.preventDefault();
    let hotelId = $('#hotels').val();

    if (hotelId == null) {
        hotelId = $('#managerHotelId').attr('data-hotel');
    }

    console.log(hotelId);

    $.ajax({
        url: $(element).attr('href'),
        type: 'GET',
        dataType: 'html',
        data: {
            'pageNumber': $(element).attr('data-page'),
            'column': $(element).attr('data-column'),
            'sort': $(element).attr('data-sort'),
            'paginate': $(element).attr('data-paginate'),
            'hotelId': hotelId,
        },
        success: function (data, status) {
            $('#reload').html(data);
            $('div.alert').reload();
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
