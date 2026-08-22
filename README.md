# Bibordle API

This repo hosts the API for retrieving the game files with the current words for the [Bibordle game](https://github.com/FXZFun/bibordle).

**Supported Translations:**
 - EHV
 - KJV

**Structure:**
 - {translation} (e.g. `ehv`)
   - `words.json`: The word list containing all valid five letter words from the Bible
   - `{int}.json`: The game file for the day specified by the number. Three game files are retained, yesterday's, today's, and tomorrow's, to support multiple time zones.
