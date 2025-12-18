// HR Messages DataTable initialization

$(document).ready(function () {
    const table = $('#messagesTable').DataTable({
        pageLength: 10,
        lengthChange: true,
        order: [[0, 'desc']], // order by internal ID
        language: {
            search: 'Search messages:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ messages',
        },
        columnDefs: [
            { targets: [0], visible: false }, // hide internal ID
        ]
    });
});
