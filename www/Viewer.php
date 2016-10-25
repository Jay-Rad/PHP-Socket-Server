<!DOCTYPE html>
<html>
<head>
    <title>InstaTech Screen Viewer</title>
    <link href="./Assets/Images/InstaTech Logo.png" rel="icon" />
    <meta name="viewport" content="width=device-width" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <script src="./Scripts/jquery-3.1.0.js"></script>
    <script src="./Scripts/jquery-ui.js"></script>
    <script src="./Scripts/adapter.js"></script>
    <script src="./Scripts/screenViewer.js"></script>

</head>
<body>
    <div id="divConnect" style="background-color: lightgray; margin-left: -8px; margin-top: -8px; width:100%; height:100%; position: fixed">
        <div style="position:absolute; top: 50%; left:50%; transform:translate(-50%, -50%); border:5px inset white; background-color:lightskyblue; border-radius: 10%; width:275px; height: 250px">
            <div style="text-align:center; font-family: monospace; font-size: 2em; font-weight: bold; position:relative; top: -80px">
                InstaTech<br />
                Screen Viewer
            </div>
            <div style="display:inline-block; position:absolute; top: 50%; left:50%; transform:translate(-50%, -50%);">
                <div style="font-family: sans-serif; font-weight: bold">Connect to client:</div>
                <input id="inputSessionID" placeholder="Enter client's session ID." type="text" pattern="[0-9 ]*" onkeypress="if (event.keyCode == 13) { connectToClient() }" style="margin: 5px 0; width:175px" />
                <br />
                <div style="text-align:right">
                    <button id="buttonConnect" onclick="connectToClient()">Connect</button>
                </div>
                <div id="divStatus" style="font-family: sans-serif; margin-top:10px; font-size:.8em"></div>
            </div>
            <div style="position: absolute; bottom: -45px; left: 10px; font-size:.8em; font-family: sans-serif">
                <label>Client download link:</label>
                <br />
                <input id="inputClientLink" onclick="copyClientLink()" readonly onmousedown="copyClientLink()" title="Send this link to your customer!" value="http://instatech.org:8899/ScreenViewer/InstaTech_CP.AppImage" style="cursor: pointer; background-color:transparent; border:none; color:blue; width:400px" />
            </div>
        </div>
    </div>
    <div id="divMain" hidden>
        
        <img id="imgMenu" src="./Assets/Images/MenuDown.png" onclick="toggleMenu()" />
        <div id="divMenu">
            <div style="font-weight: bold; margin: -5px -10px 15px -10px; padding: 5px 0 5px 0; text-align:center; background-color:rgb(25,25,25); color:white">Options</div>
            <div>
                <span>Refresh screen:</span>
                <button id="buttonRefresh" title="Refresh the entire screen." onclick="sendRefreshRequest()">
                    <img src="./Assets/Images/Refresh_16x.svg" style="height:20px; width:20px" />
                </button>
            </div>
            <div style="margin-top: 20px;">
                <span>Scale to fit:</span>
                <div id="divScaleToFit" status="on" class="switch-outer" onclick="toggleScaleToFit()">
                    <div class="switch-inner"></div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <span style="margin-bottom:5px;">File Transfer:</span>
                <button type="button" style="float:right;" onclick='$("#inputFile").click()'>Upload...</button>
                <div id="divUploadHint" style="font-size: .8em; font-family:sans-serif; color:dodgerblue; cursor:pointer" onclick='showTooltip(this, "bottom", "black", "You can also drag-and-drop files onto the window to upload them.");'>(Hint!)</div>
                <input id="inputFile" type="file" hidden multiple onchange='transferFiles(this.files); showTooltip($("#divUploadHint"), "bottom", "seagreen", "File(s) uploaded successfully.");' />
            </div>
            <div style="margin-top: 20px;">
                <span>Resolution:</span>
                <select style="float:right" onchange="changeResolution(event)">
                    <option value="25">25%</option>
                    <option value="50">50%</option>
                    <option selected value="75">75%</option>
                    <option value="100">100%</option>
                </select>
            </div>
            <div id="divImageQuality" style="margin-top: 20px;">
                <span>Image Quality:</span>
                <select style="float:right" onchange="changeImageQuality(event)">
                    <option value=".25">25%</option>
                    <option value=".50">50%</option>
                    <option selected value="75">75%</option>
                    <option value="1">100%</option>
                </select>
            </div>
            <div style="margin-top: 20px; margin-bottom:20px; height: 60px;">
                <span style="margin-bottom:5px;">Send Clipboard:</span>
                <textarea id="textClipboard" placeholder="Paste text here." style="float:right; margin-left: 10px; width:100px; resize: none;" oninput="sendClipboard(event)"></textarea>
            </div>
            <button type="button" onclick="disconnect()" style="position:absolute; bottom:5px; right:5px">Disconnect</button>
        </div>

        <canvas id="canvasScreenViewer" class="input-surface"></canvas>
        <video id="videoScreenViewer" class="input-surface" autoplay></video>
    </div>
    <style>
        #divMenu {
            position: fixed;
            background-color: lightcyan;
            border: 1px solid gray;
            min-width: 200px;
            left: 50%;
            transform: translateX(-50%);
            top: 40px;
            display: none;
            padding: 0 10px 20px 10px;
            z-index: 1;
        }

        #imgMenu {
            position:fixed;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 25px;
            cursor: pointer;
            transition-property: transform;
            transition-duration: .4s;
        }

        #imgMenu:hover {
            border: 1px groove deepskyblue;
            background-color: paleturquoise;
        }
        #imgMenu.rotated180 {
            transform: translateX(-50%) rotateZ(180deg) rotateY(180deg);
            transition-property: transform;
            transition-duration: .4s;
        }
        .input-surface {
            width: 100%;
            margin-top: 30px;
            border: 1px groove gray;
            background-color: lightgray;
            display:none;
        }

        #buttonRefresh {
            margin-left: 10px;
            cursor: pointer;
            height: 25px;
            width: 25px;
            padding: 0;
            vertical-align: bottom;
            background-color: transparent;
            border-color: transparent;
            float:right;
        }

        #buttonRefresh:hover {
            border-color: lightblue;
        }

        .switch-outer {
            height: 25px;
            width: 45px;
            float: right;
            border-radius: 10px;
            cursor: pointer;
            display: inline-block;
        }

        .switch-outer[status="on"] {
            background-color: lightskyblue;
            transition: .5s;
        }

        .switch-outer[status="off"] {
            background-color: lightgray;
            transition: .5s;
        }

        .switch-inner {
            border-radius: 100%;
            background-color: white;
            height: 20px;
            width: 20px;
            cursor: pointer;
            position: relative;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .switch-outer[status="on"] .switch-inner {
            margin-left: 24%;
            transition: .5s;
        }

        .switch-outer[status="off"] .switch-inner {
            margin-left: -24%;
            transition: .5s;
        }
    </style>
</body>
</html>
