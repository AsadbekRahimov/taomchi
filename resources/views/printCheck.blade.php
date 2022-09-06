<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Kokand trade productivity MCHJ</title>
</head>
<style>
    * {
        font-size: 14px;
        font-family: 'Times New Roman';
    }

    td,
    th,
    tr,
    table {
        border-top: 1px solid black;
        border-collapse: collapse;
    }

    td.description,
    th.description {
        width: 190px;
        max-width: 190px;
    }


    td.price,
    th.price {
        width: 110px;
        max-width: 110px;
        word-break: break-all;
    }

    .centered {
        text-align: center;
        align-content: center;
    }

    .ticket {
        width: 300px;
        max-width: 300px;
    }

    img {
        max-width: inherit;
        width: inherit;
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

    @media    print {
        .hidden-print,
        .hidden-print * {
            display: none !important;
        }
    }
</style>

<body>
<div class="ticket">
    <!--   <img  src="https://rtmedlineuz.com/site/img/logo.png" alt="">-->
    <p class="centered"><b>Kokand trade productivity MCHJ <br>Тел: 998 90 308 84 14 | Нодирхон</b>
    <p><b>Мижоз: {{ $order->customer->name }}</b>
    <b> | Карздорлиги: {{ number_format($order->customer->duties->sum('duty')) }}</b>
    <table class="table table-bordered" >
        <thead>
        <tr>
            <th style="border: 1px solid black;">№</th>
            <th style="border: 1px solid black;"  class="description">Махсулот</th>
            <th style="border: 1px solid black;">Нарх</th>
            <th style="border: 1px solid black;" class="price">Жами</th>
        </tr>
        </thead>
        <tbody>

        @foreach($order->cards as $card)
            <tr>
                <td style="border: 1px solid black;" >1</td>
                <td style="border: 1px solid black;" class="description"> {{ $card->product->name }} </td>

                <td style="border: 1px solid black;  white-space: nowrap; padding: 0 2px;">{{ number_format($card->price) }}
                    <b>x</b> {{ $card->quantity . ' ' . $card->product->measure->symbol }}</td>
                <td style="border: 1px solid black;" class="price"> {{ number_format($card->price * $card->quantity) }}</td>
            </tr>
        @endforeach

        <tr>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;" colspan="2" class="description"><b>Олинган махсулотар суммаси</b></td>
            <td style="border: 1px solid black;" class="price"><b>{{ number_format($order->cardsSum() - $order->discount) }}</b></td>
        </tr>

        <tr>
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;" colspan="2" class="description"><b>Жами карздорлиги</b></td>
            <td style="border: 1px solid black;" class="price"><b>{{ number_format($order->cardsSum() - $order->discount + $order->customer->duties->sum('duty')) }}</b></td>
        </tr>

        </tbody>
    </table>
    <p>Сана: {{ $order->created_at->toDateTimeString() }}</p>

</div>
<button id="btnPrint" class="hidden-print">Print</button>
<a href="http://metal.edokon.ru/admin/orders"><button class="hidden-print">Back</button></a>
<script>
    window.onload = function() { window.print(); }
    const $btnPrint = document.querySelector("#btnPrint");

    $btnPrint.addEventListener("click", () => {
        window.print();
    });
</script>
</body>
</html>
