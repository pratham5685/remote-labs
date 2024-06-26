<?php
$mysqli = new mysqli("sql308.infinityfree.com","if0_36221294", "XdhQn78NXjxQ4NC", "if0_36221294_booking_calender");
if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare('select * from booking where date = ?');
    $stmt->bind_param('s', $date);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result -> num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['timeslot'];
            }
            $stmt->close();
        }  
    }
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $timeslot = $_POST['timeslot'];
    $stmt = $mysqli->prepare("select * from booking where date = ? AND timeslot = ?");
    $stmt->bind_param('ss', $date, $timeslot);
    
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Already Booked</div>";

        } else{
            $stmt = $mysqli->prepare("INSERT INTO booking (name, email,date,timeslot) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $name,  $email, $date, $timeslot);
            $stmt->execute();
            $msg = "<div class='alert alert-success'>Booking Successful</div>";
            $bookings[] = $timeslot;
            $stmt->close();
            $mysqli->close();
        } 
    }
}

$duration = 60;
$cleanup = 0;
$start = "06:00";
$end = "24:00";

function timeslots($duration, $cleanup, $start, $end){
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT".$duration."M");
    $cleanupinterval = new DateInterval("PT".$cleanup."M");
    $slots = array();

    for($intStart = $start; $intStart<$end; $intStart->add($interval)->add($cleanupinterval)){
        $endPeriod = clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end){
            break;
        } 
        $slots[] = $intStart->format("H:iA")."-".$endPeriod->format("H:iA");

    }
    return $slots;

}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel = "stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <style>
            .back_button{
                position: absolute;
                top: 5%;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="back_button">
                <span><a href="calender.php"><button type="button" id="btn" class="btn btn-success">Back</button></a></span>
            </div>
            <h1 class="text-center">Book for Date: <?php echo date('d/m/Y',strtotime($date))?> </h1><hr>
            <div class="row">
                <div class="col-md-12">
                    <?php echo isset($msg) ? $msg: "" ;?>
                </div>
             <?php 
             $timeslots = timeslots($duration, $cleanup, $start, $end);
             foreach($timeslots as $ts){
             ?>
             <div class="col-md-2">
                <div class="form-group">
                    <?php if(in_array($ts, $bookings)){?>
                        <button class="btn btn-danger"><?php echo $ts; ?></button>
                        <?php }else{?>
                     <button class="btn btn-success book" data-timeslot="<?php echo $ts;?>"><?php echo $ts; ?></button>
                     <?php }?>
                    
                </div>
                
             </div>
             <?php } ?>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="myModal" role="dialog" >
        <div class="modal-dialog" >
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
                <h5 class="modal-title">Booking: <span id="slot"></span></h5>
                
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="">Timeslot</label>
                                <input type="text" readonly name="timeslot" id="timeslot" class="form-control" required >
                            </div>
                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control" required >
                            </div>
                            <div class="form-group">
                                <label for="">Email</label>
                                <input type="email" name="email" class="form-control" required >
                            </div>
                            <div class="form-group pull-right">
                            <button type="submit" name="submit" class="btn btn-primary">Book</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                
            </div> -->
            </div>
        </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script>
            $(".book").click(function(){
                var timeslot = $(this).attr('data-timeslot');
                $("#slot").html(timeslot);
                $("#timeslot").val(timeslot);
                $("#myModal").modal("show");

            });
        </script>
    </body>
</html>