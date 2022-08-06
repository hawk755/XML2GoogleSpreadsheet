# XML2GoogleSpreadsheet

This program processes a local or remote XML file and pushes the data of that XML file to a Google Spreadsheet via the
Google Sheets API (https://developers.google.com/sheets/).

PHP v8.0.2+

[Instructions]

1/ Configure authentication against Google API in .env file:

(GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET)

2/ Command to import XML file to a Google Spreadsheet:

php bin/console import XML_FILE_PATH_OR_URL

where XML_FILE_PATH_OR_URL is full(relative) path or URL to XML file