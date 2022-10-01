@extends('layouts.app')

<link rel="stylesheet" href="{{asset('css/BouncyBall.css')}}">

@section("content")
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-8 col-8 col-lg-8">
            <div id="gameFrame">
                <!-- Bar for choosing a ball  -floating right- -->
                <div class="row float-right stage" style="position:absolute;">
                    <div class="col-md-8">
                        <div class="col-md-2 col-sm-2 col-lg-2 col-2 ballStage">
                            <div id="ball1">easy</div>
                        </div>
                        <div class="col-md-1 col-sm-1 col-lg-1 col-1 ballStage"></div>
                        <div class="col-md-2 col-sm-2 col-lg-2 col-2 ballStage">
                            <div id="ball2">mid</div>
                        </div>
                        <div class="col-md-1 col-sm-1 col-lg-1 col-1 ballStage"></div>
                        <div class="col-md-2 col-sm-2 col-lg-2 col-2 ballStage">
                            <div id="ball3" class="ball bubble">hard</div>
                        </div>
                    </div>
                    <div class="col-md-4 justify-content-center" style="text-align:center;">
                        <h4 id="score">
                            Score: <span id="scoreValue">0</span>
                        </h4>
                    </div>
                </div>
                <div class="row justify-content-center gameIntroContent">
                    <div class="col-md-4 col-sm-10 col-12">
                        <button id="playBtn" class="btn btn-primary">Play</button>
                    </div>
                </div>
                <div class="row" style="height:30px;">
                    <div class="col-md-12">

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-2 col-2 col-lg-2"></div>
                    <div class="col-md-3 col-sm-3 col-3 col-lg-3">
                        <div class="row">
                            <div class="col-12" style="height:65px;">

                            </div>
                        </div>
                        <div id="ball"></div>
                    </div>
                    <div class="col-md-3 col-sm-3 col-3 col-lg-3"></div>
                    <div class="col-md-2 col-sm-2 col-2 col-lg-2" id="obstacleContainer">
                        <div id="obstacle">

                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2 col-2 col-lg-2"></div>
                </div>
                <div class="row" style="padding-left:0px;padding-right:0px;margin:0px;">
                    <div class="col-12" style="height:38px;margin-top:-2.5px;" id="ground"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Controller</div>
                <div class="card-body" style="padding:0 1.25rem 0 1.25rem;">
                    <p>
                        Hi, say <b>Play</b> to start/restart the game, or <b>Back</b> to end the game.
                        Just make the ball jump by saying any word except the 2 previous words!.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    // Find a solution to import the script!!!
    //import TweenLite from "<?php echo asset('js/TimelineLite.js') ?>";
    window.onload = function() {
        let gameOptions = {
            difficulty: {
                easy: {
                    repeatObstacle:1500,
                    maxObstacles:5,
                    moveSpeed:2.8,
                },
                medium: {
                    repeatObstacle:1250,
                    maxObstacles:8,
                    moveSpeed:2.7,
                },
                hard: {
                    repeatObstacle:1100,
                    maxObstacles:10,
                    moveSpeed:2.4,
                }
            },
            xAxisCrashCoordinate: {
                // lg(1200, inf) sm(768,991) md(992, 1199) xs(-inf, 767)
            //screen size:[ball-on-left-crash, -ball-right-tangent, ball-right-crash]
                lg:[260,305,310],
                md: [254,305,315],
                sm: [239,300,320],
                xs: [220,310,325]
            },
            getXCord() {
                if(window.innerWidth >= 1200) {
                    return gameOptions.xAxisCrashCoordinate.lg;
                }
                else if(window.innerWidth <= 1199 && window.innerWidth >= 992) {
                    return gameOptions.xAxisCrashCoordinate.md;
                }
                else if(window.innerWidth <= 991 && window.innerWidth >= 768) {
                    return gameOptions.xAxisCrashCoordinate.sm;
                }
                else {
                    return gameOptions.xAxisCrashCoordinate.xs;
                }
            },
            scoreIncrement:10,
            ballLevel: {
                easy:function(){
                    if(ball.hasAttribute("class")) {
                        ball.removeAttribute("class");
                    }
                    ball.setAttribute("id", "ball1");
                    return gameOptions.difficulty.easy;
                    },
                medium:function(){
                    if(ball.hasAttribute("class")) {
                        ball.removeAttribute("class");
                    }
                    ball.setAttribute("id", "ball2");
                    return gameOptions.difficulty.medium;
                    },
                hard:function(){
                    if(!ball.hasAttribute("class")) {
                        ball.setAttribute("class", "ball bubble");
                    }
                    ball.setAttribute("id", "ball3");
                    return gameOptions.difficulty.hard;
                    }
            }
        }
        let tl = new TimelineLite();
        let ballAnimation; // intro moving ball
        let currentDifficutly = gameOptions.difficulty.easy;
        let currentPassingObstacleIndex = 0;
        let _obstacleHeight = 7;
        let playBtn = document.getElementById("playBtn");
        let obstacleI = document.getElementById("obstacle"); // obstacle in the intro
        let scoreDocument = document.getElementById("score");
        let ground = document.getElementById("ground"); // black ground
        let stage = document.getElementsByClassName("stage")[0]; // stage of 3 balls
        let score = document.getElementById("scoreValue");
        let obstacle = obstacleI.cloneNode(true); // cloning for obstacleI
        let ball = document.getElementById("ball");
        let arrayOfObstacles = [];
        let playingInterval;
        let addScore = "";
        window.onresize = function() {
            crashPoint = gameOptions.getXCord();
        }
        function intro() {
            //  if 100% = 1366px -> 16% = 218.56px
            // Animating intro for first impression
            obstacleAnimation = new TweenMax(obstacleI, 2, {'marginLeft':'-350%'});
            ballAnimation = new TweenMax(ball, 1, {top:-150, yoyo:true, repeat:-1},'-=1.7');
            obstacleAnimation.play();
            ballAnimation.play();
            // make the obstacle ready for cloning
            obstacle.setAttribute('class', 'obstacle');
            // Add event listeners to balls view/stage
            document.getElementById("ball1").addEventListener("click",function() {
                currentDifficutly = gameOptions.ballLevel.easy();
            });
            document.getElementById("ball2").addEventListener("click",function() {
                currentDifficutly = gameOptions.ballLevel.medium();
            });
            document.getElementById("ball3").addEventListener("click",function() {
                currentDifficutly = gameOptions.ballLevel.hard();
            });

        }
        function Obstacle(height) {
            // old approach: create element -> style -> append

            // new approach: clone element -> style / 2 -> append
            //FIND A WAY TO DETECT PASSING AN OBSTACLE
            console.log(this.innerWidth);
            obstacle.style.height = height + "rem"; // set height
            obstacle.style.marginTop = (_obstacleHeight - height) + "rem"; // marginTop = originalHeight - this.height
            document.getElementById("obstacleContainer").appendChild(obstacle);
            arrayOfObstacles.push(obstacle);
            let motion = new TimelineLite();
            motion.to(obstacle, 0.2, {opacity:1,lazy:true})
            .to(obstacle, currentDifficutly.moveSpeed ,{'marginLeft':'-400%',lazy:true,
                onUpdate:function(){
                    if(hitTest(ball, arrayOfObstacles[currentPassingObstacleIndex])) {
                        // stop : obstacle, ball, game
                        this.kill();
                        ballAnimation.kill();
                        clearInterval(playingInterval);
                        let playBtnShow = new TweenMax(playBtn, 1, {'opacity':'1','visiblity':'visible'});
                        playBtnShow.play();
                        playBtn.innerHTML = "play";
                        playBtn.addEventListener('click', play);
                        stage.style.visibility = "visible";
                    }
                },onComplete:function(){
                this.kill;
                currentPassingObstacleIndex--;
                arrayOfObstacles.shift();
                document.getElementsByClassName("obstacle")[0].parentNode.removeChild(document.getElementsByClassName("obstacle")[0]);
                },lazy:true
            })
            obstacle = obstacle.cloneNode(true);
         }
        function createObstacle() {
            if(arrayOfObstacles.length <= currentDifficutly.maxObstacles) {
                // make a random value that represent waiting time to create a new obstacle
                let random = Math.floor(Math.random() * 500) + 500;
                // random height between 3 - 7
                let randomHeight = Math.floor(Math.random() * Math.floor(_obstacleHeight / 2)+1) + Math.ceil(_obstacleHeight / 2);
                setTimeout(Obstacle(randomHeight), random);
            }
        }

        function remToPx(n) {
            return (114 * (filterStyle(n) / 7));
        }

        function hitTest(obj1, obj2) {
            var ball = obj1.getBoundingClientRect();
            var obstacle = obj2.getBoundingClientRect();
            if(window.scrollX + obstacle.left <=window.scrollX + ball.right && window.scrollX +obstacle.right >= window.scrollX + ball.left) {
                if(window.scrollY + ball.top >= window.scrollY + obstacle.top-30) {
                    return true;
                }
                addScore = (addScore == "" ? setTimeout(function(){score.innerHTML=parseInt(score.innerHTML)+gameOptions.scoreIncrement;addScore="";currentPassingObstacleIndex++},500) : addScore);
                return false;
            }
            return false;
        }
        function filterStyle(str) {
            // function to remove measure units from the style and return a pure numeric literal
            str = str.replace("px", "");
            str = str.replace("rem", "");
            return parseInt(str);
        }

        function keybr(event) {
            // function to get the char of key pressed
            if (window.event) {
                keypress = event.keyCode
            }
            checkkey = String.fromCharCode(keypress);
            return checkkey.toLowerCase();
        };

        function jump() {
            let ballTop = parseInt(ball.style.top.replace("px", ""));
            if(ballTop >= -10) {
                ballAnimation = new TweenMax(ball, 0.5, {top:-150,yoyo:true,repeat:1,onComplete:function(){this.kill()}});
                ballAnimation.play();
            }
        }
        function startGame() {
            let playBtnHide = new TweenMax(playBtn, 1, {'opacity':'0','visiblity':'hidden'});
            playBtnHide.play();
            ball.style.top = "-2px";
            stage.style.visibility = "hidden";
            scoreDocument.style.visibility = "visible";
            arrayOfObstacles = [];
            currentPassingObstacleIndex = 0;
            document.getElementById("obstacleContainer").innerHTML = "";
            ball.style.height = "-2px";
            playingInterval = setInterval(createObstacle, currentDifficutly.repeatObstacle);
            document.addEventListener('keyup', function(e) {
                if(keybr(e) == "w") {
                    jump();
                }
            })
        }
        function play() {
            if(playBtn.innerHTML == "play") { // the game is played once and over
                startGame();
            }
            else {
                // first play
                ballAnimation.vars.onRepeat = function() {
                    let ballTop = parseInt(ball.style.top.replace("px", ""));
                    if(ballTop >= -5) {
                        this.kill();
                        obstacleI.parentNode.removeChild(obstacleI);
                        ground.style.backgroundColor = "black";
                        startGame();
                    }
                };
            }
            score.innerHTML = "0";
            playBtn.removeEventListener("click",play);            
        }
            playBtn.addEventListener('click', play);
            intro();
    }
</script>