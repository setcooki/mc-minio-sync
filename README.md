# mc-minio-sync

*Sync add-on for wp-minio-sync.*

This script will sync the minio s3 media library with your wordpress media library via https://github.com/setcooki/wp-minio-sync/ and https://github.com/Interfacelab/ilab-media-tools.
All media files missing in your wordpress install will be created with this script. NOTE! this is script is uni directional only - it will not do a resync and delete media files in your wordpress
media library that do not exists in your minio media library.

Run via cli:

```
php -f ./sync.php --target=<target> --bucket=<bucket> --token=<token> --url=<url>
```

Arguments:

- `--mc`: expects the minio client executable path or alias
- `--target`: expects the minio host target
- `--bucket`: expects the minio bucket
- `--token`: expects the wp-minio-sync plugin access token - see https://github.com/setcooki/wp-minio-sync
- `--url`: expects wp-minio-sync plugin sync script url - see https://github.com/setcooki/wp-minio-sync/blob/master/sync.php
- `--delay`: add a delay in seconds after each file sync