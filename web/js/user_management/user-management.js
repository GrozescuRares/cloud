$(document).ready(function () {

    $('#hotels').change(function () {
        let hotelId = $('#hotels').val();
        $.ajax({
            url: window.href,
            type: 'POST',
            dataType: 'json',
            data: {'hotelId': hotelId},
            async: true,

            success: function (data, status) {
            },
            error: function (xhr, textStatus, errorThrown) {
                alert('Ajax request failed.');
            }
        });

    });

    $(document).on('click', '.pagination li a', {}, function (event) {
        paginate(event, this);
    });

    $(document).on('click', 'th.columns', {}, function (event) {
        sort(event, this);
    });
});

function paginate(event, element)
{
    event.preventDefault();

    $.ajax({
        url: $(element).attr('href'),
        type: 'GET',
        dataType: 'html',
        data: {'type': $(element).attr('data-role'), 'pageNumber': $(element).attr('data-page')},
        async: true,

        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}

function sort(event, element)
{
    event.preventDefault();
    let sortType = $(element).attr('data-sort');

    $.ajax({
        url: $(element).attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {'type': $(element).attr('data-role'), 'pageNumber': $(element).attr('data-page'), 'column': $(element).attr('data-column'), 'sort': $(element).attr('data-sort')},

        success: function (data, status) {

            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}

