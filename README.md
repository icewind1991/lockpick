# Lockpick

Debug transactional locking conflicts

## How

- Enable the app
- Create a conflict
- Run `occ lockpick:show`
- Debug

## Example output

```text
Conflict detected between 2 locks
exclusive lock from
  OC\Files\Storage\Common->changeLock           - lib/private/Files/Storage/Wrapper/Wrapper.php 632
  OC\Files\Storage\Wrapper\Wrapper->changeLock  - lib/private/Files/Storage/Wrapper/Wrapper.php 632
  OC\Files\Storage\Wrapper\Wrapper->changeLock  - lib/private/Files/View.php 2001
  OC\Files\View->changeLock                     - apps/dav/lib/Connector/Sabre/Node.php 446
  OCA\DAV\Connector\Sabre\Node->changeLock      - apps/dav/lib/Connector/Sabre/File.php 191
  OCA\DAV\Connector\Sabre\File->put             - apps/dav/lib/Connector/Sabre/Directory.php 151
  OCA\DAV\Connector\Sabre\Directory->createFile - 3rdparty/sabre/dav/lib/DAV/Server.php 1098
  Sabre\DAV\Server->createFile                  - 3rdparty/sabre/dav/lib/DAV/CorePlugin.php 504
  Sabre\DAV\CorePlugin->httpPut                 - 3rdparty/sabre/event/lib/WildcardEmitterTrait.php 89
  Sabre\DAV\Server->emit                        - 3rdparty/sabre/dav/lib/DAV/Server.php 472
  Sabre\DAV\Server->invokeMethod                - 3rdparty/sabre/dav/lib/DAV/Server.php 253
  Sabre\DAV\Server->start                       - 3rdparty/sabre/dav/lib/DAV/Server.php 321
  Sabre\DAV\Server->exec                        - apps/dav/appinfo/v1/webdav.php 85
  require_once                                  - remote.php 167

shared lock from
  OC\Files\Storage\Common->acquireLock          - lib/private/Files/Storage/Wrapper/Wrapper.php 610
  OC\Files\Storage\Wrapper\Wrapper->acquireLock - lib/private/Files/Storage/Wrapper/Wrapper.php 610
  OC\Files\Storage\Wrapper\Wrapper->acquireLock - lib/private/Files/Cache/Scanner.php 149
  OC\Files\Cache\Scanner->scanFile              - apps/files_sharing/lib/External/Scanner.php 57
  OCA\Files_Sharing\External\Scanner->scanFile  - lib/private/Files/Cache/Scanner.php 340
  OC\Files\Cache\Scanner->scan                  - apps/files_sharing/lib/External/Scanner.php 39
  OCA\Files_Sharing\External\Scanner->scan      - lib/private/Files/Cache/Updater.php 125
  OC\Files\Cache\Updater->update                - apps/dav/lib/Connector/Sabre/File.php 368
  OCA\DAV\Connector\Sabre\File->put             - apps/dav/lib/Connector/Sabre/Directory.php 151
  OCA\DAV\Connector\Sabre\Directory->createFile - 3rdparty/sabre/dav/lib/DAV/Server.php 1098
  Sabre\DAV\Server->createFile                  - 3rdparty/sabre/dav/lib/DAV/CorePlugin.php 504
  Sabre\DAV\CorePlugin->httpPut                 - 3rdparty/sabre/event/lib/WildcardEmitterTrait.php 89
  Sabre\DAV\Server->emit                        - 3rdparty/sabre/dav/lib/DAV/Server.php 472
  Sabre\DAV\Server->invokeMethod                - 3rdparty/sabre/dav/lib/DAV/Server.php 253
  Sabre\DAV\Server->start                       - 3rdparty/sabre/dav/lib/DAV/Server.php 321
  Sabre\DAV\Server->exec                        - apps/dav/appinfo/v1/webdav.php 85
  require_once                                  - remote.php 167
```

## Commands

- `occ lockpick:list` show all stored conflict traces
- `occ lockpick:show <id>` show a stored trace, defaults to the latest
- `occ lockpick:clear` remove all stored traces
