<?php

namespace Mini\Model;

use PDO;

/* CREATE TABLE IF NOT EXISTS submissions (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    twitch_id varchar(20) NOT NULL,
    name varchar(535) CHARACTER SET utf8 NOT NULL,
    description text NOT NULL,
    date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    type int(1) unsigned NOT NULL DEFAULT 0,
    channel varchar(535) CHARACTER SET utf8 DEFAULT NULL,
    channel_id varchar(20) DEFAULT NULL,
    offline boolean DEFAULT NULL,
    online boolean DEFAULT NULL,
    ismod boolean DEFAULT NULL,
    following int(10) unsigned DEFAULT NULL,
    following_channel boolean DEFAULT NULL,
    bio text DEFAULT NULL,
    vods boolean DEFAULT NULL,
    verified boolean DEFAULT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 */

class Submissions extends PaginatingStore {
    const SUBMISSION = 0;
    const CORRECTION = 1;

    function __construct(PingablePDO $db, int $pageSize = 50)
    {
        parent::__construct($db, "submissions", $pageSize);
    }

    public function append(
        string $id,
        string $username,
        $type,
        int $correction = self::SUBMISSION,
        ?string $channel = null,
        ?string $channelId = null
    ): void
    {
        $query = $this->prepareInsert("(twitch_id,name,description,type,channel,channel_id) VALUES (?,?,?,?,?,?)");
        $params = [ $id, $username, $type, $correction, $channel, $channelId ];

        $query->execute($params);
    }

    /**
     * @return Submission[]
     */
    public function getSubmissions(?int $type = null): array
    {
        $condition = "ORDER BY score DESC, online DESC, date DESC";
        $params = [];
        if(isset($type)) {
            $condition = "WHERE type=? ".$condition;
        }
        $query = $this->prepareSelect("*, (IFNULL(offline,0) + IFNULL(ismod,0) + COALESCE(following<10,0) - IFNULL(vods,1) - (description REGEXP '^[^0-9].*$') - (2 - IFNULL(online+online,0))) AS score", $condition);
        if(isset($type)) {
            $query->bindValue(1, $type, PDO::PARAM_INT);
        }
        $query->execute();

        return $query->fetchAll(PDO::FETCH_CLASS, Submission::class);
    }

    /**
     * @return Submission|bool
     */
    public function getSubmission(int $id)
    {
        $query = $this->prepareSelect("*", "WHERE id=?");
        $query->execute(array($id));


        $query->setFetchMode(PDO::FETCH_CLASS, Submission::class);
        return $query->fetch();
    }

    public function getSubmissionOrThrow(int $id): Submission
    {
        $sub = $this->getSubmission($id);
        if(!$sub) {
            throw new \Exception("Submission does not exist");
        }
        return $sub;
    }

    public function has(string $id, ?int $type = null, ?string $description = null): bool
    {
        $where = "WHERE twitch_id=?";
        $params = array($id);

        if(!empty($type)) {
            $where .= " AND type=?";
            $params[] = $type;
        }
        if(!empty($description)) {
            $where .= " AND description=?";
            $params[] = $description;
        }
        $query = $this->prepareSelect("*", $where);
        $query->execute($params);

        return !empty($query->fetch());
    }

    public function hasSubmission(string $id): bool
    {
        return $this->has($id, self::SUBMISSION);
    }


    public function hasCorrection(string $id, string $description): bool
    {
        return $this->has($id, self::CORRECTION, $description);
    }


    public function setInChat(int $id, ?bool $inChannel = null, ?bool $live = null): void
    {
        if($live) {
            $sql = "online=? WHERE id=?";
        }
        else {
            $sql = "offline=? WHERE id=?";
        }

	    $query = $this->prepareUpdate($sql);
	    $query->execute([ $inChannel, $id ]);
    }

    public function setModded(int $id, bool $isMod): void
    {
        $sql = "ismod=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $isMod, $id ]);
    }

    public function setFollowing(int $id, int $followingCount = null): void
    {
        $sql = "following=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $followingCount, $id ]);
    }

    public function setFollowingChannel(int $id, bool $followingChannel = null): void
    {
        $sql = "following_channel=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $followingChannel, $id ]);
    }

    public function setBio(int $id, string $bio = null): void
    {
        if(!empty($bio)) {
            $sql = "bio=? WHERE id=?";
            $query = $this->prepareUpdate($sql);
            $query->execute([ $bio, $id ]);
        }
    }

    public function setHasVODs(int $id, bool $hasVODs): void
    {
        $sql = "vods=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $hasVODs, $id ]);
    }

    public function setVerified(int $id, bool $verified): void
    {
        $sql = 'verified=? WHERE id=?';
        $query = $this->prepareUpdate($sql);
        $query->execute([ $verified, $id ]);
    }

    public function setTwitchID(int $id, string $twitchID, string $type = "twitch"): void
    {
        $sql = $type."_id=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $twitchID, $id ]);
    }

    public function updateName(int $id, string $name): void
    {
        $sql = "name=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $name, $id ]);
    }

    public function updateChannelName(int $id, string $name): void
    {
        $sql = "channel=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $name, $id ]);
    }

    public function clearChannel(int $id): void
    {
        $sql = "channel=NULL,channel_id=NULL,following_channel=NULL,offline=NULL,online=NULL,ismod=NULL WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $id ]);
    }

    public function updateDescription(int $id, string $description): void
    {
        $sql = "description=? WHERE id=?";
        $query = $this->prepareUpdate($sql);
        $query->execute([ $description, $id ]);
    }

    public function removeSubmission(int $id): void
    {
        $query = $this->prepareDelete("WHERE id=?");
        $query->execute([ $id ]);
    }

    public function removeSubmissions(string $twitchID, ?string $description = null): void
    {
        $condition = "WHERE twitch_id=?";
        $args = [ $twitchID ];
        if(!empty($description)) {
            $condition .= " AND description=?";
            $args[] = $description;
        }
        $query = $this->prepareDelete($condition);
        $query->execute($args);
    }
}
