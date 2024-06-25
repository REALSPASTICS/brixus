<?php

//error_reporting(E_ALL);

include 'site/config.php';
include("site/obfuscator.php");

//if(!$loggedIn) {exit;}

function avatarToken() {
	$potatoCharacters = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnmm1234567890";
	$potatoOutput = "";
	for($repeats = 0; $repeats <= 20; $repeats++) {
		$potatoOutputOffsetDeterminator = rand(0, strlen($potatoCharacters) - 1);
		$potatoOutput .= $potatoCharacters[$potatoOutputOffsetDeterminator];
	}
	return $potatoOutput;
}

$_SESSION['SAVE_AVATAR_TOKEN'] = avatarToken();

$is_admin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT `isAdmin` FROM `users` WHERE `id` = ".intval($_SESSION["userId"])))["isAdmin"];

if(!isset($_GET["id"])) {$renderID = intval($_SESSION["userId"]);}
if(isset($_GET["id"])) {
	$renderID = intval($_GET["id"]);
}

$avatarQuery = mysqli_query($conn, "SELECT `headc`, `torsoc`, `leftarmc`, `rightarmc`, `leftlegc`, `rightlegc`, `shirt`, `face`, `hat` FROM `users` WHERE `id` = $renderID");
$avatar = mysqli_fetch_assoc($avatarQuery);

include("site/brick_colours.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex">
<noscript>
</noscript>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.84/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.84/examples/js/libs/tween.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.84/examples/js/libs/stats.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.84/examples/js/loaders/OBJLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.84/examples/js/controls/OrbitControls.js"></script>
</head>
<body style="margin:0">
<style>
* {
	font-family: Arial, sans-serif;
}
</style>
<table valign="middle" align="center" style="text-align:center;min-height:100%" height="100%" >
<tr>
<td valign="middle">
<div>Please wait</div>
<img src="/assets/loading.gif" title="Loading Preview..">
</td>
</tr>
</table>
<canvas id="avatarCanvas" width="300" height="300" style="display:none"></canvas>
<script>
<?php

$zoom = 3;

if($avatar["hat"] > 0) {
	$findHat = mysqli_query($conn,"SELECT * FROM `items` WHERE `id`=".intval($avatar["hat"]));
	$hatRow = mysqli_fetch_assoc($findHat);
	$hatZoom = $hatRow["zoom"];
	$zoom = $hatZoom;
}



if($avatar['shirt'] != 0) {
	$zoomQuery = mysqli_query($conn, "SELECT `zoom` FROM `items` WHERE `id` = {$avatar['shirt']}");
	//$zoom = mysqli_fetch_assoc($zoomQuery)['zoom'];
}




if($avatar["hat"] > 0) {
	$lookAtVector = $hatRow["lookAtVector"];
} else {
	$lookAtVector = "0, 1.2, 0";
}

$extraShit = '';
if($avatar['shirt'] != 0) {
	$shirthashquery = mysqli_query($conn, "SELECT `hash` FROM `items` WHERE `id` = {$avatar['shirt']}");
	$shirthash = mysqli_fetch_assoc($shirthashquery)['hash'];
	$extraShit .= "
	var loader = new THREE.ImageLoader(manager);
	//texture
	loader.load('/storage/items/textures/$shirthash.png?c=".rand(1000,9999)."', function(shirtimg) {
		shirttex.image = shirtimg;
		shirttex.needsUpdate = true;
	});
	
	//uv map
    var loader = new THREE.OBJLoader(manager);
    loader.load('TShirt.obj', function(shirt) {
		shirt.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = shirttex;
                child.material.transparent = true;
            }
        });
        shirt.position.y = -1.5;
		shirt.position.x = 0;
		shirt.position.z = 0;
        scene.add(shirt);
    }, onProgress, onError);
";
}

if($avatar["hat"] > 0) {
	$extraShit .= "    var loader = new THREE.OBJLoader(manager);
    loader.load('/storage/items/meshes/$hatRow[hash].obj', function(hat) {
        hat.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = hattex;
                child.material.transparent = true;
            }
        });
        hat.position.y = 3.6;
        hat.position.x = 0;
        scene.add(hat);
    }, onProgress, onError);";
}

if($avatar['face'] != 0) {
	$facehash = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hash FROM items WHERE id = ".$avatar["face"]))["hash"];
}

$threeJs = "var container;
var camera, scene, renderer;
var mouseX = 0,
    mouseY = 0;
renderer = new THREE.WebGLRenderer({
    alpha: true,
    canvas: document.getElementById('avatarCanvas'),
    antialias: true,
    preserveDrawingBuffer: true // so that the outcome isn't just empty
});
init();
var ambientLight = new THREE.AmbientLight(0xbbbbbb, 1);
scene.add(ambientLight);

function init() {
    container = document.createElement('div');
    document.body.appendChild(container);
    camera = new THREE.PerspectiveCamera(100, 300 / 300, 0.1, 1000);
    camera.position.set(2.7, 3.8, 6.5);
    camera.zoom = $zoom;
	camera.lookAt(new THREE.Vector3(0,1.2+(3-$zoom),0));

    camera.updateProjectionMatrix();


    scene = new THREE.Scene();


    var directionalLight = new THREE.DirectionalLight( 0x666666 );
    directionalLight.position.set( 0, 2, 1 );
    scene.add( directionalLight );


    var manager = new THREE.LoadingManager();
    manager.onProgress = function(item, loaded, total) {
        //console.log(item, loaded, total);
    };


    var onProgress = function(xhr) {
        if (xhr.lengthComputable) {
            var percentComplete = xhr.loaded / xhr.total * 100;
            //console.log(Math.round(percentComplete, 2) + '% downloaded');
        }
    };
    var onError = function(xhr) {};
    //TEXTURE
    var facetex = new THREE.Texture();
	var studtex = new THREE.Texture();
	var shirttex = new THREE.Texture();
	var hattex = new THREE.Texture();

    // Load textures 
    var loader = new THREE.ImageLoader(manager);
	
    loader.load('".($avatar["face"] ? "/storage/items/textures/$facehash" : "face").".png?c=".rand(1000,9999)."', function(faceimg) {
        facetex.image = faceimg;
        facetex.needsUpdate = true;
    });
	
	loader.load('studs.png?c=".rand(1000,9999)."', function(studimg) {
		studtex.image = studimg;
		studtex.needsUpdate = true;
	});
	
	loader.load('/storage/items/textures/$hatRow[hash].png',function(hatimg){
		hattex.image=hatimg;
		hattex.needsUpdate=true;
	});

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('Head_NEW.obj', function(head) {
        head.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['headc']]});
            }
        });
        head.position.y = -1.5;
        head.position.x = 0;
        head.scale = 2;
        scene.add(head);
    }, onProgress, onError);
    //uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('Head.obj', function(head) {
        head.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = facetex;
                child.material.transparent = true;
            }
        });
        head.position.y = -1.4;
        head.position.x = 0;
		head.position.z = .0001;
        scene.add(head);
    }, onProgress, onError);

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('Torso.obj', function(torso) {
        torso.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['torsoc']]});
            }
        });
        torso.position.y = -1.5;
        torso.position.x = 0;
        torso.scale = 2;
        scene.add(torso);
    }, onProgress, onError);
	//uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('Torso.obj', function(torso) {
        torso.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = studtex;
                child.material.transparent = true;
            }
        });
        torso.position.y = -1.5;
        torso.position.x = 0;
        scene.add(torso);
    }, onProgress, onError);

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('LeftArm.obj', function(larm) {
        larm.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['leftarmc']]});
            }
        });
        larm.position.y = -1.5;
        larm.position.x = 0;
        larm.scale = 2;
        scene.add(larm);
    }, onProgress, onError);
	//uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('LeftArm.obj', function(larm) {
        larm.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = studtex;
                child.material.transparent = true;
            }
        });
        larm.position.y = -1.5;
        larm.position.x = 0;
        scene.add(larm);
    }, onProgress, onError);

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('RightArm.obj', function(rarm) {
        rarm.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['rightarmc']]});
            }
        });
        rarm.position.y = -1.5;
        rarm.position.x = 0;
        rarm.scale = 2;
        scene.add(rarm);
    }, onProgress, onError);
	//uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('RightArm.obj', function(rarm) {
        rarm.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = studtex;
                child.material.transparent = true;
            }
        });
        rarm.position.y = -1.5;
        rarm.position.x = 0;
        scene.add(rarm);
    }, onProgress, onError);

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('RightLeg.obj', function(rleg) {
        rleg.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['rightlegc']]});
            }
        });
        rleg.position.y = -1.5;
        rleg.position.x = 0;
        rleg.scale = 2;
        scene.add(rleg);
    }, onProgress, onError);
	//uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('RightLeg.obj', function(rleg) {
        rleg.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = studtex;
                child.material.transparent = true;
            }
        });
        rleg.position.y = -1.5;
        rleg.position.x = 0;
        scene.add(rleg);
    }, onProgress, onError);

    // model
    var loader = new THREE.OBJLoader(manager);
    loader.load('LeftLeg.obj', function(lleg) {
        lleg.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.color.setHex(0x{$colors[$avatar['leftlegc']]});
            }
        });
        lleg.position.y = -1.5;
        lleg.position.x = 0;
        lleg.scale = 2;
        scene.add(lleg);
    }, onProgress, onError);
		//uv map of it
    var loader = new THREE.OBJLoader(manager);
    loader.load('LeftLeg.obj', function(lleg) {
        lleg.traverse(function(child) {
            if (child instanceof THREE.Mesh) {
                child.material.map = studtex;
                child.material.transparent = true;
            }
        });
        lleg.position.y = -1.5;
        lleg.position.x = 0;
        scene.add(lleg);
    }, onProgress, onError);
	
	$extraShit
	





}

function render() {
    renderer.render(scene, camera);
    requestAnimationFrame(render);
}

render();

if(window.jQuery) {
	setTimeout(function() {
		$.ajax({
			url: '/save_avatar.php',
			method: 'POST',
			data: {
				'token': '{$_SESSION['SAVE_AVATAR_TOKEN']}',
				'avatarData': document.getElementById('avatarCanvas').toDataURL(),
				'renderID':$renderID
			},
			success: function(){
				$.ajax({
					url:'/avatar/getb64.php',
					method:'POST',
					success:function(z){window.location='/avatar/getavatarsized.php';}
				});
			}
			
		});
	}, 5000);
}
";

$hunter = new HunterObfuscator($threeJs);

$hunter->addDomainName("www.brixus.net");

echo $hunter->Obfuscate();

// echo $threeJs;
?>
</script>
</body>
</html>
