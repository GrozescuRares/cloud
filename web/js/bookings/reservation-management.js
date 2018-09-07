$(document).ready(function () {
    $(document).on('click', '.delete-reservation', {}, function (event) {
        deleteReservation(event, this);
    });
});

function deleteReservation(event, element)
{
    event.preventDefault();

    $.ajax({
        url: $(element).attr('href'),
        type: 'GET',
        dataType: 'html',
        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
