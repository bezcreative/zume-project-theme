<?php
/*
Template Name: Zúme Dashboard
*/

/**
 * Gets all available coaches in the system who have the coach role.
 * @return array
 */
function zume_get_coach_ids () {
    $result = get_users( array('role' => 'coach') );
    $coaches = array();
    foreach($result as $coach) {
        $coaches[] = $coach;
    }
    return $coaches;
}

/**
 * Gets all coaches in a particular group, returns an array of integers
 * @return array
 */
function zume_get_coach_ids_in_group ($group_id) {
    global $wpdb;
    if (is_numeric($group_id)) {
        $group_id = (int) $group_id;
    } else {
        throw new Exception("group_id argument should be an integer or pass the is_numeric test.");
    }
    $results = $wpdb->get_results( "SELECT $wpdb->usermeta.user_id FROM $wpdb->bp_groups_members INNER JOIN $wpdb->usermeta ON $wpdb->usermeta.user_id=$wpdb->bp_groups_members.user_id WHERE group_id = '$group_id' AND meta_key = '$wpdb->capabilities' AND meta_value LIKE '%coach%'", ARRAY_A );
    $rv = [];
    foreach ($results as $result) {
        $rv[] = (int) $result["user_id"];
    }
    return $rv;
}


/**
 * Returns the User ID of assigned_to field
 *
 * @param $assigned_to
 * @return string|bool          If team or dispatch, returns false.
 */
function zume_get_assigned_to_user_id ($assigned_to) {
    $user_array = explode('-', $assigned_to);

    if($user_array[0] == 'dispatch') {
        return false;
    } elseif ($user_array[0] == 'group') {
        return false;
    } else { /* else it equals group */
        $userid = $user_array[1];
        $user = get_userdata( $userid );
        return $user->ID;
    }
}

/**
 * Returns $result['coaches'] (string) for the assigned_to id's of the coaches, and $result['count'] (int) with the number of groups the coach is assigned to.
 * @return array
 */
function zume_get_coaches_for_groups() {
    global $wpdb;
    $current_user = get_current_user_id();
    $result = $wpdb->get_results('
              SELECT wp_bp_groups_groupmeta.meta_value as coach_id, wp_bp_groups_groupmeta.group_id, count(meta_value) as count
              FROM wp_bp_groups_members
              INNER JOIN wp_bp_groups_groupmeta ON wp_bp_groups_groupmeta.group_id=wp_bp_groups_members.group_id
              WHERE wp_bp_groups_members.user_id = \''.$current_user.'\'
              AND wp_bp_groups_groupmeta.meta_key = \'assigned_to\'
              AND wp_bp_groups_groupmeta.meta_value != \'dispatch\'
              GROUP BY meta_value
              ', ARRAY_A);
    return $result;
}
/**
 * Returns $result['coaches'] (string) for the assigned_to id's of the coaches, and $result['count'] (int) with the number of groups the coach is assigned to.
 * @return array
 */
function zume_get_groups_for_coach($coach_id, $user_id = null) {
    global $wpdb;

    if(empty($user_id)) {
        $user_id = get_current_user_id();
    }

    $result = $wpdb->get_results("
              SELECT wp_bp_groups_groupmeta.group_id
              FROM wp_bp_groups_members
              INNER JOIN wp_bp_groups_groupmeta ON wp_bp_groups_groupmeta.group_id=wp_bp_groups_members.group_id
              WHERE wp_bp_groups_members.user_id = '$user_id'
              AND wp_bp_groups_groupmeta.meta_key = 'assigned_to'
              AND wp_bp_groups_groupmeta.meta_value = '$coach_id'
              ", ARRAY_A);

    return $result;
}

$zume_dashboard = Zume_Dashboard::instance();
$zume_current_user = get_current_user_id();
$zume_get_userLink = bp_core_get_userlink($zume_current_user, false, true);

get_header();

?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">



                <article id="post-<?php the_ID(); ?>" <?php post_class(''); ?> role="article" itemscope itemtype="http://schema.org/WebPage">

                    <header class="article-header">

                    </header> <!-- end article header -->

                    <section class="entry-content" itemprop="articleBody">


                        <!-- First Row -->
                        <div class="row" data-equalizer data-equalize-on="medium" id="test-eq">
                            <div class="medium-12 columns">
                                <div class="callout" data-equalizer-watch>
                                    <h2 class="center">Your Groups</h2>

                                    <?php if ( bp_has_groups(array('user_id' => get_current_user_id() ) ) ) : ?>

                                        <ul id="groups-list" class="item-list">
                                            <?php while ( bp_groups() ) : bp_the_group(); ?>
                                                <?php
                                                if (1 === preg_match('/[0-9]+/', bp_get_group_member_count(), $matches)) {
                                                    $member_count = $matches[0];
                                                } else {
                                                    $member_count = 0;
                                                }
                                                ?>

                                                <li>
                                                    <div class="row">
                                                        <div class="medium-5 large-5 columns gutter-medium">
                                                            <div class="item-avatar">
                                                                <a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ) ?></a>
                                                            </div>
                                                            <div>
                                                                <div class="item-title">
                                                                    <a href="<?php bp_group_permalink() ?>"><?php bp_group_name() ?></a>
                                                                </div>
                                                                <div class="wp-caption-text">
                                                                    <?php echo bp_get_group_description_excerpt() ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="medium-3 large-3 columns gutter-medium center dashboard-group-images">
                                                            <?php if (bp_group_has_members(['group_id' => bp_get_group_id(), 'exclude_admins_mods' => false, 'per_page' => 11, 'max' => 11])): ?>
                                                                <?php
                                                                while (bp_group_members()) {
                                                                    bp_group_the_member();
                                                                    bp_group_member_avatar();
                                                                    /* It's important that we don't print spaces between avatars */
                                                                }
                                                                ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="medium-4 large-4 columns gutter-medium center">
                                                            <div class="button-group">

                                                                <a href="<?php echo bp_get_group_permalink(). 'group_invite_by_url/'; ?>" class=" button  ">Invite <?php if($member_count < 4 ) { echo (4 - $member_count) . ' more to start'; } ?></a>

                                                                <span class="hide-for-medium"><br></span>

                                                                <?php if($member_count > 3 ): // check if there are minimum 4 members ?>

                                                                    <?php // Next session button
                                                                    $group_next_session = zume_group_next_session(bp_get_group_id());

                                                                    if (is_null($group_next_session)): ?>
                                                                        <a href="/zume-training/?id=10&group_id=<?php bp_group_id() ?>" class="button   ">See Sessions</a>
                                                                    <?php else: ?>
                                                                        <a href="/zume-training/?id=<?php echo $group_next_session; ?>&group_id=<?php bp_group_id() ?>" class="button   ">Start Session <?php echo $group_next_session; ?></a>
                                                                    <?php endif; ?>


                                                                <?php endif; ?>
                                                            </div>
                                                        </div>


                                                        <div class="clear"></div>
                                                    </div>
                                                </li>

                                            <?php endwhile; ?>
                                        </ul>


                                    <?php else: ?>
                                      <div style="background: url('<?php echo get_stylesheet_directory_uri() . '/assets/images/noun_attention.png'; ?>') top left no-repeat;
                                          padding-left:55px;
                                          background-size:50px">
                                        <p style="padding: 10px"><strong>You are currently not in a group.</strong><br>
                                          You will need at least four people gathered together to start each new session. Please start a group below.
                                          If you intended to join someone else's group, please return to the invitation they sent and use
                                          the link provided to be automatically added to that group.
                                      </div>

                                    <?php endif; ?>

                                    <p class="center">
                                        <a href="/groups/create/" class="button">Start New Group</a>
                                    </p>
                                </div>
                            </div>
                        </div>



                        <!-- Second Row -->
                        <div class="row" data-equalizer data-equalize-on="medium" id="test-eq">
                            <div class="medium-8 columns dashboard-coaches" id="your-coaches">

                                <div class="callout" data-equalizer-watch>

                                    <?php $result = zume_get_coaches_for_groups(); ?>

                                    <?php if ( !empty($result) ) : ?>

                                    <h2 class="center"><?php echo _n("Your Coach", "Your Coaches", count($result)) ?></h2>

                                        <?php foreach($result as $value) : $coach_id = substr($value['coach_id'], 5); $group_ids = array(); $group_ids[] = $value['group_id']; if($value['count'] > 1) { $group_ids = zume_get_groups_for_coach($value['coach_id'], get_current_user_id()); } ?>

                                            <ul id="groups-list" class="item-list">

                                                <li class="coach-item">

                                                    <div class="coach-item__intro">

                                                        <a href="<?php echo  wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $coach_id ) ); ?>" class="btn button" style="margin-bottom: 0">
                                                            Private Message</a>
                                                        <a href="<?php echo bp_core_get_userlink($coach_id, false, true) ?>"><?php  echo bp_core_fetch_avatar( array( 'item_id' => $coach_id) ) ?></a>

                                                    </div>

                                                    <div class="coach-item__text">

                                                        <?php  echo bp_core_get_userlink($coach_id); ?>

                                                        <?php _e("for groups:"); ?>

                                                        <?php $i = 0; foreach ($group_ids as $group_id) : $group = groups_get_group($group_id) ?>

                                                            <a href="<?php bp_group_permalink($group); ?>">
                                                                <?php bp_group_name($group); ?>
                                                            </a>

                                                            <?php $i++; if(count($group_ids) > 1 && $i < count($group_ids) ) { echo '-'; } ?>

                                                        <?php endforeach ?>

                                                    </div>
                                                </li>
                                            </ul>

                                        <?php endforeach; ?>


                                    <?php else: ?>

                                        <h2 class="center">Your Coach</h2>
                                        <div style="background: url('<?php echo get_stylesheet_directory_uri() . '/assets/images/noun_attention.png'; ?>') top left no-repeat;
                                            padding-left:55px;
                                            background-size:50px">
                                          <p style="padding: 10px">
                                            Every group will be assigned a live coach who will help mentor you during the disciple-making process and help keep you accountable.
                                            When your coach is assigned you'll see his/her name here and you'll be able to send a private message.
                                          </p>
                                        </div>

                                    <?php endif; ?>


                                </div>
                            </div>


                            <div class="medium-4 columns dashboard-messages">
                                <div class="callout" data-equalizer-watch>
                                    <h2 class="center">Messages</h2>

                                    <p class="center" style="margin-top: 15px">
                                        <a href="<?php echo bp_core_get_userlink($zume_current_user, false, true) ; ?>/messages/" class="button">View Messages</a>
                                    </p>
                                    <p class="center text-gray text-small">
                                        <?php
                                        $unread_messages_count = bp_get_total_unread_messages_count();
                                        echo sprintf(_n("You have %s unread message.", "You have %s unread messages.", $unread_messages_count), $unread_messages_count);
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>



                    </section> <!-- end article section -->

                    <footer class="article-footer">

                    </footer> <!-- end article footer -->



                </article> <!-- end article -->



            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
