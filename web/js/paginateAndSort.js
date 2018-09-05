$(document).ready(function () {

    $(document).on('click', '.pagination li a', {}, function (event) {
        paginateAndSort(event, this);
    });

    $(document).on('click', 'th.columns', {}, function (event) {
        paginateAndSort(event, this);
    });
});

function paginateAndSort(event, element) {
    event.preventDefault();
    let hotelId = $('#hotels').val();

    $.ajax({
        url: $(element).attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {
            'type': $(element).attr('data-role'),
            'pageNumber': $(element).attr('data-page'),
            'column': $(element).attr('data-column'),
            'sort': $(element).attr('data-sort'),
            'hotelId': hotelId,
            'paginate': $(element).attr('data-paginate')
        },
        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
