# Ayuda para imprimir

## Flujo de los datos para imprimir

Primero llamamos desde algún lugar de nuestro código donde queremos imprimir, al **Printaitor** haciendo 'Printaitor::send($dataToView, $printerName, $viewName)'
$dataToView es un array con todas las variables que podran ser accedidas desde el archivo .ctp correspondiente a esa impresora fiscal
$printerName es el nombre que identifica a la impresora fiscal
$viewName es, básicamente el nombre de lo que yo quiero imprimir

$printerName se basa en el campo $driverName configurado para esa impresora. De acuerdo a ese valor selecciona el /lib/DriverView, o sea, la vista que debe renderizar.
Por el momento el tipo de driver disponible es Fiscal o Receipt, y es facilmente extensible desde la carpeta /lib/DriverView.
Por último tenemos los helpers, que son variantes dentro del un driver para diferentes modelos de impresoras. O sea, por ejemplo, podemos tener una impresora que usa el driver fiscal y el modelo es Hasa441. Se pueden agregar nuevos modelos desde la carpeta /Lib/DriverView/Helper.
Por lo tanto, cada impresora debe tener configurado un driver y un modelo.

No siempre la variable $viewName va a tener su correspondencia con un archivo .ctp. Se pueden dar ocaciones en las que esa impresora no presente una vista para la acción requerida. En ese caso, no pasa nada, el sistema sigue funcionando y no envia ni excepciones ni nada.

La salida, llamada "Output", por el momento es general y se configura desde el archivo de configuración como 'Printers.output = File' o 'Printers.output = Cups' o 'Printers.output = Database'. Es fácilmente extensible creando nuevos Outputs en lib/PrinterOutput



