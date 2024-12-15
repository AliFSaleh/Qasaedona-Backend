<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<title>Email</title>
    <link rel="icon" href="img/favicon.svg">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap');
        body {
            margin: 0;
            padding: 0;
            font-family: 'Cairo', sans-serif;
            background: #F8F8F8
        }
        p {
            margin: 0;
            padding: 0;
        }
        .wrapper {
            background: #F8F8F8;
            width: 600px;
            margin: auto;
        }
        .main-table {
            color: #231F20;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-spacing: 0;
        }
    </style>
    
</head>

<body>
    <center class="wrapper">
        <table class="main-table" width="100%" style="">
            <tr>
                <td style="text-align: center;padding: 10px;">
                    <table width="100%">
                        <tr>
                            <td style="border-radius: 15px;text-align: center;padding: 20px;">
                                {{-- <a href=""><img src="{{url('uploads/logo.png')}}" alt="" title="Logo" width="250px" /></a> --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--Logo-->
        
            <tr>
                <td style="text-align: center;padding: 15px; padding-bottom: 100px;">
                    <table width="100%" style="background: #fff;padding: 40px 20px;border-radius: 10px">
                        <tr>
                            <td style="text-align: center">
                                <strong style="color:#033F52;font-size:22px;margin-bottom:30px;font-weight:800;display:block;text-transform: uppercase;">Verifiy your email</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                {{$token}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!--ph-->
    
        </table>
    </center>
</body>
</html>