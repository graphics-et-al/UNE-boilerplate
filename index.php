<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport">
    <title data-react-helmet="true">Assignment blurb generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <style>
        @font-face {
            font-family: "FontAwesome";
            font-style: normal;
            font-weight: 400;
            src: url(//netdna.bootstrapcdn.com/font-awesome/4.0.3/fonts/fontawesome-webfont.eot?v=4.0.3);
            src: url("//netdna.bootstrapcdn.com/font-awesome/4.0.3/fonts/fontawesome-webfont.eot?#iefix&v=4.0.3") format("embedded-opentype"), url(//netdna.bootstrapcdn.com/font-awesome/4.0.3/fonts/fontawesome-webfont.woff?v=4.0.3) format("woff"), url(//netdna.bootstrapcdn.com/font-awesome/4.0.3/fonts/fontawesome-webfont.ttf?v=4.0.3) format("truetype"), url("//netdna.bootstrapcdn.com/font-awesome/4.0.3/fonts/fontawesome-webfont.svg?v=4.0.3#fontawesomeregular") format("svg")
        }

        .attostylesbox {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            width: auto;
            position: relative;
            padding: .75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem
        }

        .attostylesbox p {
            margin-bottom: 0;
        }

        .attostylesbox--outline.attostylesbox--info {
            background-color: transparent;
            border-color: #004085;
            color: #000;
            padding-left: 3rem
        }

        .attostylesbox--outline.attostylesbox--info:before {
            content: "";
            font-family: FontAwesome;
            color: #004085;
            position: absolute;
            top: .8rem;
            left: 1.5rem
        }

        .attostylesbox--outline.attostylesbox--info:after {
            content: "";
            position: absolute;
            width: .5rem;
            height: 100%;
            border-radius: .25rem;
            top: 0;
            left: 0;
            display: block;
            background-color: #cce5ff
        }

        .attostylesbox--solid.attostylesbox--callout {
            background-color: #d9f0f7;
            border-color: #a1e5ea;
            color: #0f3a3e;
        }

        h3 {
            font-weight: 700;
            margin-top: 10px;
            margin-bottom: 10px;
            padding-top: .3em;
            padding-bottom: .3em;
            overflow: hidden;
        }
    </style>
    <!-- Sweetalert for popupds-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const copyToClipboard = el_id => {
            var element = document.getElementById(el_id);
            navigator.clipboard.writeText(element.innerHTML + "<hr/>").then(
                function() {
                    /* Alert the copied text */
                    Swal.fire({
                        showClass: {
                            popup: "animate__animated animate__backInDown"
                        },
                        hideClass: {
                            popup: "animate__animated animate__backInUp"
                        },
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 2000,
                        icon: "info",
                        title: "HTML copied to clipboard"
                    });
                    /* clipboard successfully set */

                    navigator.clipboard.readText().then(
                        function(e) {},
                        function() {
                            alert("Clipboard failed");
                        }
                    );
                },
                function(e) {
                    //console.log(e);
                    alert("Copy and pasting not supported in this browser");
                }
            );
        };
    </script>
</head>

<body>
    <div class="container p-32">
        <a href="../" class="btn btn-secondary position-absolute top-0 start-0 m-2">&laquo Back</a>
        <h1 class="mt-4">Moodle element generator</h1>
        <form method="GET">
            <div class="mb-3">
                <label for="unitcode" class="form-label">Unit code</label>
                <input type="text" class="form-control" value="<?php print(strtoupper($_GET['unitcode'])) ?>" name="unitcode">
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="text" class="form-control" name="year" value="<?php print($_GET['year']) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <h2 class="mt-16">Results</h2>
        <div id="resultsGrid" class="border">
            <!-- The assessment strings appear here -->

        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>


        <?php
        /////////////////////////////////////////////////////////////////
        // Gather data
        /////////////////////////////////////////////////////////////////

        // WS02 credentials
        $key = "WS02 key from ITD here";
        $secret = "WS02 secret from ITD here";

        

        // standard cURL request
        // get the access token. It only lasts an hour, later we'll be getting a permanent one
        $options = array(
            CURLOPT_URL => 'https://apigateway-une-prod.une.edu.au/token',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode("$key:$secret"),
            ],
            //CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $access_token = json_decode($response)->access_token;
        $unitcode = strtoupper($_GET['unitcode']);
        $year = $_GET['year'];

        // get the actual data. First get the cl_id
        // update parameters
        $options[CURLOPT_URL] = "https://apigateway-une-prod.une.edu.au/courses-and-units/3.2.0/unit/$year/$unitcode/cl_id";
        $options[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer  ' . $access_token,
        ];
        $options[CURLOPT_POST] = 0;
        $options[CURLOPT_HTTPGET] = 1;
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        // this is the courseloop ID for the course
        $cl_id = json_decode($response)->cl_id;

        // get unit info
        $options[CURLOPT_URL] = "https://apigateway-une-prod.une.edu.au/courses-and-units/3.2.0/unit/$cl_id";
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        // this is the unit overview- we get the UC details from here, mostly
        $unit_info = json_decode($response);

        // get assessment info for the unit
        $options[CURLOPT_URL] = "https://apigateway-une-prod.une.edu.au/courses-and-units/3.2.0/unit/$cl_id/assessments";
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $assessments = json_decode($response);

        // get learning outcomes info- we map the learning outcomes cl_id from the assessment info later
        $options[CURLOPT_URL] = "https://apigateway-une-prod.une.edu.au/courses-and-units/3.2.0/unit/$cl_id/learning-outcomes";
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $learning_outcomes = json_decode($response);
    
        //////////////////////////////////////////////////////////////
        // Start output
        //////////////////////////////////////////////////////////////

        // contact details
        print('<h2 class="mt-16">UC contact details</h2>');

        // Loop through the unit coordinators listed in the unit info
        foreach ($unit_info->unit_coordinator as  $j => $contact) {
            print($contact->offerings[0] . '<br/>');
            $contacttemplate = " <div style='text-align: left;' class='attostylesbox attostylesbox--outline attostylesbox--info'>
            <span><strong>Unit Coordinator:</strong>&nbsp; %UC%<br></span>
            <strong>Email:&nbsp; </strong>%EMAIL%<br>
            <strong>Preferred contact method:&nbsp;</strong>An email message is the most reliable way of contacting me directly. I will attempt to respond to all enquiries in a timely manner. In your email, please state which unit you are referring to, because I may be responding to inquiries about several units.</div>";
            print("<div > <p><strong>{$contact->full_name}</strong></p><div id=\"contact_{$j}\">" . str_replace(array('%UC%', '%EMAIL%'), array($contact->full_name, $contact->email),  $contacttemplate) . "</div></div>");
            // The copy button
            print("<div class='w-100'><button class='btn btn-outline-primary' onclick='copyToClipboard(\"contact_{$j}\")'>Copy</button></div><hr/>");
        }

        // Assessment tasks
        print('<h2 class="mt-16">Assessment tasks</h2>');
        
        // loop through the assessment tasks, display
        foreach ($assessments as  $k => $assessment) {

            // Build the Learning Outcomes string by map the learning outcomes cl_id from the assessment info            
            $learning_outcomes_str = "";
            // an array for convenience...
            $related_outcomes_arr = [];
            foreach ($assessment->related_outcomes as $related_outcome) {
                // get the related outcome string that matches the cl_id of the related outcome array in teh assessments
                $internal_related_outcomes_arr = array_filter($learning_outcomes, function ($k) use ($related_outcome) {
                    return $k->cl_id == $related_outcome;
                });
                // add it to the array
                $related_outcomes_arr[] = "ULO" . reset($internal_related_outcomes_arr)->reference_id;
            }
            // build the string by a bit of array magic. Puts a ', ' between the entries
            $learning_outcomes_str = implode(', ', explode(' ', implode(' ', $related_outcomes_arr)));

            // A template string- we'll do a search replace for teh relevant bits
            $insidetemplate = "
    <div class='attostylesbox attostylesbox--outline attostylesbox--info'>
        <strong>Mandatory Task:</strong>&nbsp;%MUST_COMPLETE%<br/>
        <strong>Weighting:</strong>&nbsp;%WEIGHT%%<br/>
        <strong>Assessment Notes:</strong>&nbsp;%DESCRIPTION%<br/>
        <strong>Relates to Learning Outcomes:</strong>&nbsp;%LEARING_OUTCOMES%
    </div>
    ";
            // massage the description
            $description = str_replace(array("<p>", "</p>", "<br />", '<br>'), array('', "<br/>", "", ""), $assessment->notes);

            // printing out the actual thing
             // the outside (Moodle main page)
             $mandatory_str = "This assessment is a mandatory task. You must complete all mandatory assessments in order to be eligible to pass this unit.";
             $outsidetemplate = "<h3><span>%DESCRIPTION%&nbsp;</span></h3>
             <div class='attostylesbox attostylesbox--solid attostylesbox--callout'>
                 <strong>Weighting:</strong>&nbsp;%WEIGHT%%<br><strong></strong><strong>Mandatory Task:</strong>&nbsp;%MUST_COMPLETE%
             </div>";
             print("<div > <p><strong>{$assessment->assessment_title} (Label)</strong></p>
             <div id=\"outitem_{$k}\">" . str_replace('<br/><br/>', '<br/>', str_replace(array('%NAME%', '%MUST_COMPLETE%', '%WEIGHT%', '%DESCRIPTION%', '%LEARING_OUTCOMES%'), array((isset($assessment->name) ? $assessment->name : ''), (isset($assessment->compulsory) ? (($assessment->compulsory == 1) ? $mandatory_str : 'No') : 'No'), (isset($assessment->exam_weighting) ? $assessment->exam_weighting : 'N/A'), $assessment->assessment_title, $learning_outcomes_str),  $outsidetemplate) . "</div></div>"));
             // conveniently formatting the HTML
             print("<div class='w-100'>HTML<br/><pre>" . htmlentities(str_replace('<br/><br/>', '<br/>', str_replace(array('%NAME%', '%MUST_COMPLETE%', '%WEIGHT%', '%DESCRIPTION%', '%LEARING_OUTCOMES%'), array((isset($assessment->assessment_title) ? $assessment->assessment_title : ''), (isset($assessment->compulsory) ? (($assessment->compulsory == 1) ? $mandatory_str : 'No') : 'No'), (isset($assessment->exam_weighting) ? $assessment->exam_weighting : 'N/A'), $assessment->assessment_title, $learning_outcomes_str),  $outsidetemplate)) . "<hr/>") . "</pre></div>");
             // The copy button
             print("<div class='w-100'><button class='btn btn-outline-primary' onclick='copyToClipboard(\"outitem_{$k}\")'>Copy</button></div><hr/>");
           
            // Internal (inside the assessment) string
            print("<div > <p><strong>{$assessment->assessment_title} (Internal activity description)</strong></p><div id=\"item_{$k}\">" . str_replace('<br/><br/>', '<br/>', str_replace(array('%NAME%', '%MUST_COMPLETE%', '%WEIGHT%', '%DESCRIPTION%', '%LEARING_OUTCOMES%'), array((isset($assessment->name) ? $assessment->name : ''), (isset($assessment->compulsory) ? (($assessment->compulsory == 1) ? 'Yes' : 'No') : 'No'), (isset($assessment->exam_weighting) ? $assessment->exam_weighting : 'N/A'), $description, $learning_outcomes_str),  $insidetemplate) . "</div></div>"));
            // conveniently formatting the HTML
            print("<div class='w-100'>HTML<br/><pre>" . htmlentities(str_replace('<br/><br/>', '<br/>', str_replace(array('%NAME%', '%MUST_COMPLETE%', '%WEIGHT%', '%DESCRIPTION%', '%LEARING_OUTCOMES%'), array((isset($assessment->assessment_title) ? $assessment->assessment_title : ''), (isset($assessment->compulsory) ? (($assessment->compulsory == 1) ? 'Yes' : 'No') : 'No'), (isset($assessment->exam_weighting) ? $assessment->exam_weighting : 'N/A'), $description, $learning_outcomes_str),  $insidetemplate)) . "<hr/>") . "</pre></div>");
            // The copy button
            print("<div class='w-100'><button class='btn btn-outline-primary' onclick='copyToClipboard(\"item_{$k}\")'>Copy</button></div><hr/>");

           
        }
        ?>
    </div>
</body>

</html>