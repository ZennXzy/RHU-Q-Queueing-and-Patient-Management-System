
<aside class="top-navbar">
    <div style="display:flex; flex-direction:row; align-items: center;">
        <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2">
        <h3>RHU-Q: Queuing and Patient Management System</h3>
    </div>
    <div class="rightNav">
        <h3 style="font-size:20px;">
            <?php
                $username = $_SESSION['logged_name'];
                echo '● '.ucfirst($username);
            ?>
        </h3>
        <span style="font-size:10px; text-align: right;">
            <?php
                $userrole = $_SESSION['logged_role'];
                echo ucfirst($userrole);
            ?>   
        </span>
    </div>
</aside>