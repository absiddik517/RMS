<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.min.js" integrity="sha512-w3u9q/DeneCSwUDjhiMNibTRh/1i/gScBVp2imNVAMCt6cUHIw6xzhzcPFIaL3Q1EbI2l+nu17q2aLJJLo4ZYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <title>Document</title>
  <!--
  <style>
    /* Targets all the pages */
@page {
  size: 8.5in 9in;
  margin-top: 4in;
}

/* Targets all even-numbered pages */
@page :left {
  margin-top: 4in;
}

/* Targets all odd-numbered pages */
@page :right {
  size: 11in;
  margin-top: 4in;
}

/* Targets all selectors with `page: wide;` set */
@page wide {
  size: a4 landscape;
}

@page {
  /* margin box at top right showing page number */
  @top-right {
    content: "Page " counter(pageNumber);
  }
}

  </style>
  -->
  <style>
  @font-face {
            font-family: 'Nikosh';
            src: url({{ resource_path('view/Nikosh.ttf') }}) format('truetype');
        }
        * {
            font-family: 'Nikosh', sans-serif;
            font-size: 16px;
        }
    @media print {
      @page {
        size: legal landscape!important; /* Set the paper size to legal and orientation to landscape */
        margin: 10mm; /* Adjust margins as needed */
      }
      
      /* Optional: Customize the layout for printing */
      body {
        font-size: 12px; /* Adjust font size if needed */
        color:red;
      }
    }
    
    table{
      width: 11in;
      border-collapse: collapse;
    }
    table thead tr th{
      border: 1px solid #000;
    }
  </style>
</head>
<body>
  <button id="print">Print</button>
  <div id="content">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Address</th>
          <th>Test</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>রাংলা</td>
          <td>সিদ্দিক</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
  
  
  
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    $(document).ready(function (){
      $('#print').on('click', function(e){
        var element = document.getElementById('content');
        var opt = {
          margin:       1,
          filename:     'myfile.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'legal', orientation: 'landscape' }
        };
        html2pdf(element, opt);
        //window.print();
      })
    })
  </script>
</body>
</html>
