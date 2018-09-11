$(document).ready(function () {
    $(document).on('click', '.delete-reservation', {}, function (event) {
        deleteReservation(event, this);
    });
});

function deleteReservation(event, element)
{
    event.preventDefault();

    let confirmation = confirm('Are you sure that you want to delete this reservation?');
    if (!confirmation) {
        return false;
    }
    
    $.ajax({
        url: $(element).attr('href'),
        type: 'GET',
        dataType: 'html',
        data: {
            'pageNumber': $(element).attr('data-page'),
            'column': $(element).attr('data-column'),
            'sort': $(element).attr('data-sort'),
            'paginate': $(element).attr('data-paginate'),
        },
        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
