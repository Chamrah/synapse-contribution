<?php
    require_once 'model/admin.php';
    require_once 'model/users.php';
    require_once 'model/group.php';
    require_once 'model/home.php';

    function groups(){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];
        
        if (isset($_POST['submit_search_group'])) {
            $search_group = $_POST['search_group'];

            $vosgroupes = recherchervosGroupParkeywords($id,$search_group);
            $joingroupes = rechercherjoinGroupParkeywords($id,$search_group);
            $suggestiongroupes = recherchersuggestionGroupParkeywords($id,$search_group);
        } else if (isset($_POST['submit_all_group'])) {
            $vosgroupes = recherchervosGroup($id);
            $joingroupes = rechercherjoinGroup($id);
            $suggestiongroupes = recherchersuggestionGroup($id);
        } else {
            $vosgroupes = recherchervosGroup($id);
            $joingroupes = rechercherjoinGroup($id);
            $suggestiongroupes = recherchersuggestionGroup($id);
        }

        $invitations = selectinvitationgroup();
        

        require_once 'vue/group.php';
    }

    function addgroupe($name, $description,$id){
        addgroup($name, $description,$id);
        header('Location: index.php?action=groups');
    }

    function exploregroupe($id_group){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];

        $group_info = selectGroup($id_group);
        $group_info = $group_info[0];

        $likesamie = likesamiegrp($id,$id_group);

        if($group_info->id_admin == $id){
            $is_admin = true;
        }else{
            $is_admin = false;
        }

        $countmemberGroup = countmemberGroup($id_group);
        $countmemberGroup = $countmemberGroup[0];

        $countmembres = $countmemberGroup->count;

        $group_posts = selectgroupeposts($id_group);

        $group_posts_likes = selectgroupepostslikes($id);

        $countlikes = countlikesgroupe();

        $enregistrerpostes = selectenregistrementgroupepost($id,$id_group);

        $countcomment = countcommentsgroupe($id_group);

        $imgmembres = selectmembresgroupparid($id_group);

        require_once 'vue/exploregroupe.php';
    }

    function joingroup($idgroupe){
        $iduser = $_SESSION['id_user'];
        join_group($idgroupe,$iduser);
        header('Content-Type: application/text');
    }
    function canceljoingroup($idgroupe){
        $iduser = $_SESSION['id_user'];
        canceljoin_group($idgroupe,$iduser);
        header('Content-Type: application/text');
    }

    function invitationgroup($id_group){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];

        
        $group_info = selectGroup($id_group);
        $group_info = $group_info[0];

        if($group_info->id_admin == $id){
            $is_admin = true;
        }else{
            $is_admin = false;
        }

        $countmemberGroup = countmemberGroup($id_group);
        $countmemberGroup = $countmemberGroup[0];

        $countmembres = $countmemberGroup->count;

        $imgmembres = selectmembresgroupparid($id_group);

        require_once 'vue/invitationgroup.php';
    }

    function membresgroup($id_group){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];

        
        $group_info = selectGroup($id_group);
        $group_info = $group_info[0];

        if($group_info->id_admin == $id){
            $is_admin = true;
        }else{
            $is_admin = false;
        }

        $countmemberGroup = countmemberGroup($id_group);
        $countmemberGroup = $countmemberGroup[0];

        $countmembres = $countmemberGroup->count;

        $imgmembres = selectmembresgroupparid($id_group);

        require_once 'vue/membresgroup.php';
    }

    function select_invitation_group($id_group){
        $invitations = selectinvitationgroupparid($id_group);
        echo json_encode($invitations);

        header('Content-Type: application/json');
    }

    function select_membres_group($id_group){
        $id = $_SESSION['id_user'];

        $group_info = selectGroup($id_group);
        $group_info = $group_info[0];

        $membres = selectmembresgroupparid($id_group);
        echo json_encode([
            'membres' => $membres,
            'id_admin' => $group_info->id_admin
        ]);

        header('Content-Type: application/json');
    }

    function acceptinvit($id_user,$id_groupe){
        acceptinvitation($id_user,$id_groupe);
        rejectinvitation($id_user,$id_groupe);

        header('Content-Type: application/json');
    }

    function rejectinvit($id_user,$id_groupe){
        rejectinvitation($id_user,$id_groupe);

        header('Content-Type: application/json');
    }

    function kickmember($id_groupe_member){
        kickmembergroup($id_groupe_member);

        header('Content-Type: application/json');
    }

    function invitasadmin($id_groupe_member,$id_groupe){
        invitasadmingroup($id_groupe_member,$id_groupe);

        header('Content-Type: application/json');
    }

    function select_amie($id_user){
        $amis = select_amie_group($id_user);
        header('Content-Type: application/json');
        echo json_encode($amis);
    }

    function invit_amie_group($id_user,$id_groupe){
        invit_amie_groupe($id_user,$id_groupe);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    }

    function cancel_invit_group($id_user,$id_groupe){
        cancel_invit_groupe($id_user,$id_groupe);


        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    }

    function creergroupPosts($id_user,$id_groupe){
        // Récupération des données du formulaire
        $text_content = $_POST['text_content'];
        $currentDate = date("Y-m-d H:i:s");

        // Vérifier si le dossier existe, sinon le créer
        if ($_FILES['image']['name']) {
            $groupDir = $_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/img/groupes/' . $id_groupe;

            if (!is_dir($groupDir)) {
                mkdir($groupDir, 0777, true);
            }
            // Traitement de l'image
            $countpostgroupe = countpostgroupe($id_groupe);
            $countpostgroupe = $countpostgroupe->count + 1;
            //$_SERVER['DOCUMENT_ROOT'] houwa repertoire racine
            $tmpName = $_FILES['image']['tmp_name'];
            $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = $countpostgroupe . '.' . $imageExtension;
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/img/groupes/' . $id_groupe . '/'. $image;

            //kat7t f database
            $imageUrl = 'img/groupes/' . $id_groupe . '/'. $image;


            // Déplacer l'image dans le répertoire "uploads"
            move_uploaded_file($tmpName, $imagePath);
        } else {
            $imageUrl = "";
        }

        insertgroupPost($text_content, $imageUrl, $currentDate, $id_user, $id_groupe);

        header('Content-Type: application/json');
        echo json_encode([
            'id_groupe' => $id_groupe,
        ]);
    }

    function selectpostgroupinfo($id_post){
        $infos = selectpostgroupeinfo($id_post);

        header('Content-Type: application/json');
        echo json_encode($infos);
    }

    function modifierpostgroup($id_post_groupe,$text_content){

        $infos = selectpostgroupeinfo($id_post_groupe);
        $id_groupe = $infos->id_groupe;
        $oldimage = $infos->image_path_groupe;

        if ($_POST['imagehere'] === "true" && $_FILES['image']['name']) {            
            // Traitement de l'image
            // Define the directory where the images are stored
            $imageDirectory = $_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/img/groupes/' . $id_groupe . '/';
                    
            // Get all image files in the directory (you can adjust the extensions as needed)
            $imageFiles = glob($imageDirectory . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    
            // Initialize the highest number to 0
            $highestNumber = 0;
                    
            // Loop through the files to find the highest numbered image
            foreach ($imageFiles as $imageFile) {
                // Extract the number from the filename (assuming filenames are like 1.jpg, 2.jpg, 3.jpg, etc.)
                if (preg_match('/(\d+)\.(jpg|jpeg|png|gif|webp)$/i', basename($imageFile), $matches)) {
                    $imageNumber = (int)$matches[1];
                    if ($imageNumber > $highestNumber) {
                        $highestNumber = $imageNumber;
                    }
                }
            }
            
            // Increment the highest number by 1 to create the next image name
            $newImageNumber = $highestNumber + 1;

            // Delete the old image
            if($oldimage !== ""){
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/' . $oldimage)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/' . $oldimage);
                }
            }


            //$_SERVER['DOCUMENT_ROOT'] houwa repertoire racine
            
            $tmpName = $_FILES['image']['tmp_name'];
            $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = $newImageNumber . '.' . $imageExtension;
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/img/groupes/' . $id_groupe . '/'. $image;
            
            //kat7t f database
            $imageUrl = 'img/groupes/' . $id_groupe . '/'. $image;
            
            
            // Déplacer l'image dans le répertoire "uploads"
            move_uploaded_file($tmpName, $imagePath);
            $_FILES = [];

        } else {
            if($_POST['imagehere'] === "true"){
                $imageUrl = $oldimage;
            }else{
                if($oldimage !== ""){
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/' . $oldimage)) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/' . $oldimage);
                    }
                }
                $imageUrl = "";
            }
        }

        modifiergroupePost($text_content, $imageUrl, $id_post_groupe);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'text_content' => $text_content,
            'image_url' => $imageUrl,
            'id_post_groupe' => $id_post_groupe
        ]);
    }

    function supprimerPostgroup($id_post_groupe){
        supprimerPostgroupe($id_post_groupe);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'id_post_groupe' => $id_post_groupe
        ]);
    }

    function likePostgroup($id_post_groupe){
        $id_user = $_SESSION['id_user'];
        likePostgroupe($id_post_groupe,$id_user);
        $countlike = countlikespost($id_post_groupe);
        $countlike = $countlike->countlike;

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'id_post_groupe' => $id_post_groupe,
            'countlike' => $countlike
        ]);
    }

    function unlikePostgroup($id_post_groupe){
        $id_user = $_SESSION['id_user'];
        unlikePostgroupe($id_post_groupe,$id_user);
        $countlike = countlikespost($id_post_groupe);
        $countlike = $countlike->countlike;

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'id_post_groupe' => $id_post_groupe,
            'countlike' => $countlike
        ]);
    }

    function savePostgroup($id_post_groupe){
        $id_user = $_SESSION['id_user'];
        savePostgroupe($id_post_groupe,$id_user);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'id_post_groupe' => $id_post_groupe
        ]);
    }

    function unsavePostgroup($id_post_groupe){
        $id_user = $_SESSION['id_user'];
        unsavePostgroupe($id_post_groupe,$id_user);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'id_post_groupe' => $id_post_groupe
        ]);
    }

    function affichepostgroupe($id_post_groupe){
        if(isset($_SESSION['conn']) && $_SESSION['conn'] == true){
            $id = $_SESSION['id_user'];
            $user = selectuser($id);
            $fullname = $user['prenom'] . " " . $user['nom'];

            $id_groupe = selectid_groupe($id_post_groupe)->id_groupe;
            $likesamie = likesamiegrp($id,$id_groupe);

            if (!isingroup($id,$id_groupe)) {
                $_SESSION['id_groupe'] = $id_groupe;
                $id_groupe = $_SESSION['id_groupe'];
                $id_group = $id_groupe;

                $group_info = selectGroup($id_groupe);
                $group_info = $group_info[0];

                $countmemberGroup = countmemberGroup($id_groupe);
                $countmemberGroup = $countmemberGroup[0];

                $countmembres = $countmemberGroup->count + 1;

                $post = selectpostgroupe($id_post_groupe);
                $group_posts_likes = selectgroupepostslikes($id);
                $countlikes = countlikesgroupe();
                $enregistrerpostes = selectenregistrementgroupepostpartage($id,$id_post_groupe);
                $countcomment = countcommentsgroupe($id_groupe);

                if($group_info->id_admin == $id){
                    $is_admin = true;
                }else{
                    $is_admin = false;
                }

                $imgmembres = selectmembresgroupparid($id_group);

                require_once 'vue/affichepostgroupe.php';
            } else {
                $_SESSION['id_groupe'] = $id_groupe;
                $id_groupe = $_SESSION['id_groupe'];
                $group_info = selectGroup($id_groupe);
                $group_info = $group_info[0];

                $countmemberGroup = countmemberGroup($id_groupe);
                $countmemberGroup = $countmemberGroup[0];

                $countmembres = $countmemberGroup->count + 1;

                $invitations = selectinvitationgroup();

                require_once 'vue/joingroupe.php';

            }
        }else{
            $id_groupe = selectid_groupe($id_post_groupe)->id_groupe;

            $_SESSION['id_groupe'] = $id_groupe;
            $id_groupe = $_SESSION['id_groupe'];
            $group_info = selectGroup($id_groupe);
            $group_info = $group_info[0];

            $likesamie = likesamiegrp($id,$id_group);

            $countmemberGroup = countmemberGroup($id_groupe);
            $countmemberGroup = $countmemberGroup[0];

            $countmembres = $countmemberGroup->count + 1;

            $invitations = selectinvitationgroup();
            $imgmembres = selectmembresgroupparid($id_groupe);

            require_once 'vue/partagepostlogin.php';
        }
    }

    function allcomments($id_post_groupe){
        $comments = selectcommentsgroupe($id_post_groupe);
        $likesofcomment = getlikescomment($id_post_groupe);

        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;

        header('Content-Type: application/json');
        echo json_encode([
            'comments' => $comments,
            'likesofcomment' => $likesofcomment,
            'id_user' => $idm
        ]);
    }

    function submitcommentgroupe($id_groupe_post,$groupe_comment){
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;
        submitcommentgroup($idm,$id_groupe_post,$groupe_comment);

        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success'
        ]);
    }

    function submitreplygroupe($groupe_comment,$reply_to){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];

        $idm = selectidmember($id)->id_groupe_member;
        submitreplygroup($idm,$groupe_comment,$reply_to);
        $reply = selectreplygroup($idm);

        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'fullname' => $fullname,
            'photo_profile' => $reply->photo_profil,
            'date_reply' => $reply->reply_grp_at,
            'reply' => $groupe_comment
        ]);
    }

    function getresponsegroup($id_comment){
        $response = selectresponsegroup($id_comment);
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;
        $replylikes = getlikesreplycomment($id_comment);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'response' => $response,
            'id_user' => $id,
            'id_member' => $idm,
            'replylikes' => $replylikes
        ]);
    }

    function commentlike($id_comment){
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;

        submitcommentlike($id_comment,$idm);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]);
    }

    function removecommentlike($id_comment){
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;

        removeecommentlike($id_comment,$idm);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]);
    }

    function replylike($id_reply){
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;

        submitreplylike($id_reply,$idm);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]);
    }

    function removereplylike($id_reply){
        $id = $_SESSION['id_user'];
        $idm = selectidmember($id)->id_groupe_member;

        removeereplylike($id_reply,$idm);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]);
    }

    function deletegroup($id_group){
        deletegroupe($id_group);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]);
    }

    function leavegroup($id_group, $id_user){
        leavegroupe($id_group, $id_user);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success'
        ]); 
    }

    function change_group_banner($id_group) {
        if (!isset($_FILES['group_banner'])) {
            echo json_encode(['status' => 'error', 'message' => 'Aucun fichier reçu.']);
            exit;
        }
    
        $groupDir = $_SERVER['DOCUMENT_ROOT'] . '/Synapse/synapseNt/img/groupes/' . $id_group;
        if (!is_dir($groupDir)) {
            mkdir($groupDir, 0777, true);
        }
    
        // Count posts to generate unique filename
        $countpostgroupe = countpostgroupe($id_group);
        $countpostgroupe = $countpostgroupe->count + 1;
    
        $tmpName = $_FILES['group_banner']['tmp_name']; // Match AJAX key
        $imageExtension = pathinfo($_FILES['group_banner']['name'], PATHINFO_EXTENSION);
        $image = $countpostgroupe . '.' . $imageExtension;
        $imagePath = $groupDir . '/' . $image;
    
        // Move uploaded file
        if (move_uploaded_file($tmpName, $imagePath)) {
            $imageUrl = 'img/groupes/' . $id_group . '/' . $image;
    
            // Update database (Ensure function exists)
            change_groupe_banner($id_group, $imageUrl);
    
            echo json_encode(['status' => 'success', 'image_url' => $imageUrl]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Échec du téléchargement.']);
        }
    }
    
    function multimedia_groupe($id_group){
        $id = $_SESSION['id_user'];
        $user = selectuser($id);
        $fullname = $user['prenom'] . " " . $user['nom'];

        
        $group_info = selectGroup($id_group);
        $group_info = $group_info[0];

        if($group_info->id_admin == $id){
            $is_admin = true;
        }else{
            $is_admin = false;
        }

        $countmemberGroup = countmemberGroup($id_group);
        $countmemberGroup = $countmemberGroup[0];

        $countmembres = $countmemberGroup->count;

        $postes_contenu = select_postes_contenu($id_group);

        $imgmembres = selectmembresgroupparid($id_group);

        require_once 'vue/multimediagroup.php';
    }
?>