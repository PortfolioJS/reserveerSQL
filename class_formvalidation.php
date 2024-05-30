<?php

class Formvalidation
{
    protected array $postInput; //de $_POST array

    protected array $outputArray; //de gevalideerde inputarray

    protected string $headerString; //de pagina waarnaar de Output array moet worden verstuurd (via session)

    public bool $error; //False: de input v.h. formulier kan naar de actionpage.php; True: een foutmelding verschijnt in het formulier

    public string $textErr;
    public string $emailErr;
    public string $passwordErr;
    public string $newPasswordErr;
    public string $confirmNewPasswordErr;
    public array $dateErr; //ARRAY met MEERDERE foutmeldingen: om te voorkomen dat een foutmelding overschreven wordt door een volgende foutmelding krijgen ze zo een index (in het geval dat een formulier meerdere inputfields van hetzelfde inputtype heeft)
    public array $timeErr; //IDEM (de overige $Errs worden op dit moment niet meerdere keren in hetzelfde formulier gebruikt)
    public array $quantityErr; //IDEM
    public string $telErr;

    function __construct($postInput, $headerString)
    {
        $this->postInput = $postInput;
        $this->headerString = 'Location: /reserveerSQL/' . $headerString;
        $this->outputArray = [];
        $this->error = False;
    }

    public function get_headerString()
    {
        return $this->headerString;
    }

    public function get_outputArray()
    {
        return $this->outputArray;
    }

    public function removeAccents($str) //bron: https://raw.githubusercontent.com/lingtalfi/Bat/master/StringTool.php (hier staat de complete lijst)
    { //gebruikt voorafgaand aan tekstveld-validatie a-z
        $map = [

            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'Å' => 'A',

            'ç' => 'c',
            'Ç' => 'C',

            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',

            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',

            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',

            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',

        ];
        return strtr($str, $map);
    }

    public function validateForm()
    {
        foreach ($this->postInput as $key => $value) {
            if (str_contains($key, 'text')) {
                $value = $this->removeAccents($value); //de meest voorkomende acccenten worden weggehaald voorafgaand aan controle alfabetische invoer (zie functie hierboven)
                if (empty($_POST[$key])) {
                    $this->error = True;
                    $this->textErr = "Field is required.";
                } else {
                    if (!preg_match("/^[0-9a-zA-Z-' ]*$/", $value)) {
                        $this->error = True;
                        $this->textErr = "Only alphabets, numbers and whitespace are allowed.";
                    } else {
                        $text = $this->form_input($_POST[$key]);
                        $this->outputArray[$key] = $text;
                        $this->textErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$textErr must not be accessed before initialization in [...])
                    }
                }
            } else if (str_contains($key, 'email')) {
                if (empty($_POST[$key])) {
                    $this->error = True;
                    $this->emailErr = "Email is required.";
                } else {
                    $email = $this->form_input($_POST[$key]);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->error = True;
                        $this->emailErr = "Invalid email format.";
                    } else {
                        $this->outputArray[$key] = $email;
                        $this->emailErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                    }
                }
            } // else if (str_contains($key, 'password')) //weggelaten, zie hieronder. OPLETTEN bij naamgeving van de versch. HTML-inputfields voor password
            else if ($key == 'password') { //bovenstaande str_contains vervangen, om te onderscheiden tussen de verschillende soorten formulier (inlog of aanmaken nieuw wachtwoord)
                //LET OP: bovenstaande alleen gebruiken bij inlogformulier (voor formulier aanmaken nieuw wachtwoord: zie hieronder)
                if (empty($_POST[$key])) {
                    $this->error = True;
                    $this->passwordErr = "Password is required";
                } else {
                    $password = $this->form_input($_POST[$key]);
                    $this->outputArray[$key] = $password; //kan dat wel? een ONGEHASHT password in een session array zetten? ($_POST is veilig, maar geldt dat ook voor een session?)
                    $this->passwordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                }
            }
            // else if (str_contains($key, 'password')) {
            else if ($key == 'newpassword') { //bovenstaande str_contains vervangen, omdat deze if anders drie keer dezelfde output geeft
                //omdat er hieronder twee $keys worden gebruikt, staan er in de code geen abstracte $key-variabelen, maar specifieke: newpassword en confirmnewpassword
                //(opletten dus bij naamgeving van de HTML-inputfields voor newpassword en confirmnewpassword - dat die precies kloppen)  
                if (!empty($_POST["newpassword"]) && ($_POST["newpassword"] == $_POST["confirmnewpassword"])) {
                    $newPassword = $this->form_input($_POST["newpassword"]);
                    // $confirmNewPassword = $this->form_input($_POST["confirmnewpassword"]);
                    if (strlen($newPassword) < 8) {
                        $this->error = True;
                        $this->newPasswordErr = "Your password must contain at least 8 characters!";
                        $this->confirmNewPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                    } elseif (!preg_match("#[0-9]+#", $newPassword)) {
                        $this->error = True;
                        $this->newPasswordErr = "Your password must montain at least 1 number!";
                        $this->confirmNewPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])                       
                    } elseif (!preg_match("#[A-Z]+#", $newPassword)) {
                        $this->error = True;
                        $this->newPasswordErr = "Your password must contain at least 1 capital letter!";
                        $this->confirmNewPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                    } elseif (!preg_match("#[a-z]+#", $newPassword)) {
                        $this->error = True;
                        $this->newPasswordErr = "Your password must contain at least 1 lowercase letter!";
                        $this->confirmNewPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                    } else {
                        $this->outputArray[$key] = $newPassword; //het nieuwe wachtwoord kan door via de header
                        // $this->outputArray[$key] = $confirmNewPassword; //het nieuwe wachtwoord kan door via de header
                        $this->newPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                        $this->confirmNewPasswordErr = ""; //IDEM
                    }
                } elseif (!empty($_POST["newpassword"])) {
                    $this->error = True;
                    $this->newPasswordErr = "The passwords do not match.";
                    $this->confirmNewPasswordErr = "Please check you've confirmed your new password correctly!";
                } elseif (empty($_POST["newpassword"])) {
                    $this->error = True;
                    $this->newPasswordErr = "Please enter new password!";
                    $this->confirmNewPasswordErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                }
            } else if ($key == 'confirmnewpassword') { //zie hierboven bij ($key == 'confirmnewpassword') daar wordt het meeste al afgehandeld 
                // if (!empty($_POST["newpassword"]) && ($_POST["newpassword"] == $_POST["confirmnewpassword"])) {
                // $newPassword = $this->form_input($_POST["newpassword"]);
                // $confirmNewPassword = $this->form_input($_POST["confirmnewpassword"]);
                // } else
                if (empty($_POST["confirmnewpassword"])) {
                    $this->error = True;
                    $this->confirmNewPasswordErr = "Please confirm new password!";
                }
            } else if (str_contains($key, 'date')) {
                $date = $this->form_input($_POST[$key]);
                if (preg_match("/^[0-9-]*$/", $date)) {
                    if (strtotime($date) === false) {
                        $this->error = True;
                        $this->dateErr[] = "Please fill in a correct date.";
                    } else {
                        list($year, $month, $day) = explode('-', $date);
                        if (checkdate($month, $day, $year) === (False)) {
                            $this->error = True;
                            $this->dateErr[] = "Date not valid.";
                        } else {
                            $this->outputArray[$key] = $date;
                            $this->dateErr[] = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                        }
                    }
                } else {
                    $this->error = True;
                    $this->dateErr[] = "Please fill in a correct date.";
                }
            } else if (str_contains($key, 'time')) {
                $time = $this->form_input($_POST[$key]);
                if (str_contains($time, ':')) {
                    list($hours, $minutes) = explode(':', $time);
                    if (preg_match("/^\d{1,2}:\d{2}$/", $time) && (($hours < 24 && $minutes < 60 || ($hours == 24 && $minutes == 0)) == True)) {
                        $this->outputArray[$key] = $time;
                        $this->timeErr[] = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                    } else {
                        $this->error = True;
                        $this->timeErr[] = "Invalid time!";
                    }
                } else {
                    $this->error = True;
                    $this->timeErr[] = "Invalid time format! Use : to separate hours and minutes.";
                }
            } else if (str_contains($key, 'quantity')) {
                $quantity = $this->form_input($_POST[$key]);
                if (empty($_POST[$key])) {
                    $this->error = True;
                    $this->quantityErr[] = "Fill in a valid quantity.";
                } elseif (preg_match("/^[0-9]*$/", $quantity)) {
                    $this->outputArray[$key] = $quantity;
                    $this->quantityErr[] = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                } else {
                    $this->error = True;
                    $this->quantityErr[] = "Not a valid quantity.";
                }
            } else if (str_contains($key, 'tel')) {
                $tel = $this->form_input($_POST[$key]);
                if (preg_match("/([0]{1}[6]{1}[-\s]*[1-9]{1}[\s]*([0-9]{1}[\s]*){7})|([0]{1}[1-9]{1}[0-9]{1}[0-9]{1}[-\s]*[1-9]{1}[\s]*([0-9]{1}[\s]*){5})|([0]{1}[1-9]{1}[0-9]{1}[-\s]*[1-9]{1}[\s]*([0-9]{1}[\s]*){6})/", $tel)) {
                    $this->outputArray[$key] = $tel;
                    $this->telErr = ""; //LET OP: deze lege Errormelding is nodig om foutmeldingen bij de validatie te voorkomen (Fatal error: Uncaught Error: Typed property Formvalidation::$[...]Err must not be accessed before initialization in [...])
                } else {
                    $this->error = True;
                    $this->telErr = "Not a valid (dutch) phone number.";
                }
            } else if (str_contains($key, 'radio')) { //LET OP: geen if (empty($_POST[$key])): deze constructie (loopen over $_POST) herkent geen if (empty) omdat de $_POST[$key] van een lege radio niet bestaat
                $radio = $this->form_input($_POST[$key]);
                $this->outputArray[$key] = $radio;
            } else if (str_contains($key, 'checkbox')) { //LET OP: geen if (empty($_POST[$key])): deze constructie (loopen over $_POST) herkent geen if (empty) omdat de $_POST[$key] van een lege checkbox niet bestaat
                $checkbox = $_POST[$key];
                foreach ($checkbox as $day => $value) {
                    $checkedbox[] = $this->form_input($value);
                }
                $this->outputArray[$key] = $checkedbox;
            } else {
                echo "Inputfield is not valid for validation."; //het betreffende input type staat niet in deze (onvolledige) lijst of er is een typefout gemaakt in het formulier (als deze foutmelding zich nog ergens voordoet, heb ik niet goed opgelet), of eventueel heeft iemand in de html zitten prutsen in de browser
            }
        }
        return $this->outputArray;
    }

    public function form_input($userData)
    {
        $userData = trim($userData);
        $userData = stripslashes($userData);
        $userData = htmlspecialchars($userData);
        return $userData;
    }
}
