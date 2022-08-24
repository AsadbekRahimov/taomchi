<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Saxovat talim</title>
</head>
<style>
    * {
        font-size: 12px;
        font-family: 'Times New Roman';
    }


    .centered {
        text-align: center;
        align-content: center;
    }

    .ticket {
        width: 200px;
        max-width: 200px;
    }

    img {
        max-width: 120px;
        width: 120px;
    }


    .hidden-print {
        box-shadow: 0px 10px 14px -7px #276873;
        background:linear-gradient(to bottom, #024a7d 5%, #053873 100%);
        background-color:#024a7d;
        border-radius:8px;
        display:inline-block;
        cursor:pointer;
        color:#ffffff;
        font-family:Arial;
        font-size:16px;
        font-weight:bold;
        padding:5px 10px;
        text-decoration:none;
        text-shadow:0px 1px 0px #000203;
        border: none;
        outline: none;
    }
    .hidden-print:hover {
        background:linear-gradient(to bottom, #053873 5%, #024a7d 100%);
        background-color:#053873;
    }
    .hidden-print:active {
        position:relative;
        top:1px;
    }

    @media  print {
        .hidden-print,
        .hidden-print * {
            display: none !important;
        }
    }
</style>

<body>
<div class="ticket">
    <img class="centered" src="{{ asset('logo1.png') }}">
    <p><b>Tolov raqami: </b> № {{ $receipt->id }} </p>
    <p><b>O'quvchi: </b> {{ $receipt->student->fio_name }} </p>
    <p><b>Gurux: </b> {{ $receipt->group->name }} </p>
    <p><b>To'lov turi: </b> {{ \App\Models\Payment::TYPES[$receipt->type] }} </p>
    <p><b>To'lov miqdori: </b> {{ number_format($receipt->sum) }} </p>
    <p><b>To'langan vaqt: </b> {{ $receipt->created_at }} </p>

</div>
<button id="btnPrint" class="hidden-print">Print</button>
<script>
    window.onload = function() { window.print(); }
    const $btnPrint = document.querySelector("#btnPrint");

    $btnPrint.addEventListener("click", () => {
        window.print();
    });
</script>
</body>
</html>
