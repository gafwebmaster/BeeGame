<?php

session_start();


class Beegame {

    public $bees;

    public function beeDetails() {
        $beeDetails = [
            ['name' => 'queen', 'beeNumber' => 1, 'fullLife' => 100, 'damage' => -8],
            ['name' => 'worker', 'beeNumber' => 5, 'fullLife' => 75, 'damage' => -10],
            ['name' => 'drone', 'beeNumber' => 8, 'fullLife' => 50, 'damage' => -12]
        ];
        return (array) ($beeDetails);
    }

    public function countBee(array $beeDetails) {
        /*
         * Find how many bee are
         */
        $this->beeDetails = $beeDetails;
        $totalBees = array_sum(array_column($beeDetails, 'beeNumber'));
        return (int) ($totalBees);
    }

    public function randomBeeId(int $totalBees) {
        /*
         * Random number 
         */
        $this->totalBees = $totalBees;
        $randomNumber = rand(0, $totalBees - 1);
        if (!$this->isBeeAlive($randomNumber)) {
            $randomNumber = $this->getNextAliveBee($randomNumber);
        }
        return (int) ($randomNumber);
    }

    public function createBeeList(array $beeDetails) {
        if (!$_SESSION['allBees']) {
            $this->beeDetails = $beeDetails;
            $allBees = [];

            foreach ($beeDetails as $category) {
                for ($i = 0; $i < $category['beeNumber']; $i++) {
                    $allBees[] = [
                        $category['name'],
                        $category['fullLife'],
                        $category['damage']
                    ];
                }
            }

            $_SESSION['allBees'] = $allBees;
            $_SESSION['lastHit'] = 0;
        }
    }

    public function substractLifeDamage(array $beesSession, int $randomNumber) {

        $this->beesSession = $beesSession;
        $this->randomNumber = $randomNumber;

        $currentLife = $_SESSION['allBees'][$randomNumber][1] - abs($_SESSION['allBees'][$randomNumber][2]);
        if ($currentLife < 0 ) {
            $currentLife = 0;
        }

        $_SESSION['allBees'][$randomNumber][1] = $currentLife;
    }

    public function hitClick() {
        $beesInSession = $_SESSION['allBees'];
        $randomBeeRecieved = $_POST['hitValueId'];

        $_SESSION['lastHit'] = $randomBeeRecieved;

        //Substract damage-life from one random bee
        self::substractLifeDamage($beesInSession, $randomBeeRecieved);
    }
    
    public function checkIfGameOver(){
        if(!isset($_SESSION['allBees'])) {
            return false;
        }
        //Convention: quen will have all the time id=0        
        if(isset($_SESSION['allBees']) && $_SESSION['allBees'][0][1]<=0){
            return true;
        }
        $beeAliveStatus = true;   
        foreach ($_SESSION['allBees'] as $idBee=>$name){         
            if(($_SESSION['allBees'][$idBee][1]) > 0){                
                $beeAliveStatus = false;
            }            
        }
        return $beeAliveStatus;
    }
    
    public function isBeeAlive($idBee){
        
        if(($_SESSION['allBees'][$idBee][1])<=0){                
            return false;
        }    
        return true;
    }
    
    public function getNextAliveBee($idDeadBee){
        // return next bee alvive after the dead one
        foreach ($_SESSION['allBees'] as $idBee => $name){
            if ($idBee <= $idDeadBee) {
                continue;
            }
            if(($_SESSION['allBees'][$idBee][1])>0){ 
                return $idBee;     
            }
        } 
        // if nothing returened till now, it means the dead bee is the last one - than we'll return 0 (first bee)
        return 0;
    }

    
    
    public function gameOver(){
        session_destroy ();
        echo ("Game over");
    }

}

$game = new Beegame();

//Collect bees details
$beeDetails = $game->beeDetails();


//Count bees: 14
$allBeesCounted = $game->countBee($beeDetails);

//Choose random a bee id (0-13)
$randomBee = $game->randomBeeId($allBeesCounted);

//Bee listed as array of arrays added in session
if (isset($_POST['startGame']) && $_POST['username'] != '') {
    $_SESSION['username'] = $_POST['username'];
    $beesInSession = $game->createBeeList($beeDetails);
}

//Check if game over
$gameStatus = $game->checkIfGameOver();
if($gameStatus){
    $game->gameOver();
}

if ($_POST AND (isset($_POST['hitButton']))){
    $game->hitClick();
}

?>
<!DOCTYPE html>
<html>
    <head></head>
    <body>
        <form action="" method="post">
            <?php
            if (!isset($_SESSION['allBees'])) {
                if (isset($_POST['startGame']) && $_POST['username'] == '') {
                    ?>
                    Fill in a username
                    <?php
                }
                ?>
                <input type="text" name="username" value="" placeholder="User name">
                <input type="submit" name="startGame" value="Start game">            
                <?php
            } else {
                ?>
                    User <?php echo $_SESSION['username'];?>:
                    <br>

                    <input type="hidden" name="hitValueId" value="<?= $randomBee; ?>">
                    <input type="submit" name="hitButton" value="Hit">        
                    <br>
                    <br>
                    (Next hit: <?= $randomBee; ?>)
                    <br>
                    <table celpadding="5">
                        <tr>
                            <td>Id</td>
                            <td>Bee type</td>
                            <td>Life left</td>
                            <td>Damage</td>
                        </tr>
                        <?php
                            foreach ($_SESSION['allBees'] as $idBee => $name) {        
                                ?>
                                    <tr <?php echo ($_SESSION['lastHit'] == $idBee ? 'style="background: red;"' : ''); ?>>
                                        <td><?php echo $idBee;?></td>
                                        <td><?php echo $_SESSION['allBees'][$idBee][0];?></td>
                                        <td><?php echo $_SESSION['allBees'][$idBee][1];?></td>
                                        <td><?php echo $_SESSION['allBees'][$idBee][2];?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </table>
                <?php
            }
            ?>
            
        </form>
        
    </body>
</html>