<!-- test.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Datepicker Test</title>

    <!-- jQuery & jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- Font Awesome for icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap (optional for styling) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="col-md-4">
        <label class="form-label">Select Date</label>
        <div class="input-group">
            <input type="text" id="hold_date" class="datepicker form-control" value="06/14/2021">
            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(".datepicker").datepicker({
            dateFormat: "mm/dd/yy",
            changeMonth: true,
            changeYear: true,
            yearRange: "1900:2050"
        });

        // Open calendar when icon is clicked
        $(".input-group-text").on("click", function () {
            $(this).siblings("input").datepicker("show");
        });
    });
</script>

</body>
</html>
