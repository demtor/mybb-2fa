import re
import shutil
from pathlib import Path

package_dirs_blacklist = ('test', 'doc')
package_files_blacklist = (
    '.gitignore',
    'readme',
    'changelog',
)
package_file_extensions_blacklist = ('yml', 'xml', 'dist', 'json', 'lock', 'neon')

# dir: regex_filename
specific_paths_blacklist = {
    '': r'^autoload\.php$',
    'composer': r'^(?!installed\.json).*$',
    'paragonie/constant_time_encoding/src': r'^(?!(?:Base32|EncoderInterface|Binary)\.php).*$'
}

dirs_to_remove = []
files_to_remove = []

current_dir = Path()

for file in current_dir.glob("*/*/*"):
    if (
        file.is_file() and
        file.name.lower().startswith(package_files_blacklist) or
        file.suffix[1:].lower().startswith(package_file_extensions_blacklist)
    ):
        files_to_remove.append(str(file))
    elif (
        file.is_dir() and
        file.name.lower().startswith(package_dirs_blacklist)
    ):
        dirs_to_remove.append(str(file))

for dir, regex_filename in specific_paths_blacklist.items():
    for file in Path(dir).iterdir():
        if re.search(regex_filename, file.name):
            if file.is_file():
                files_to_remove.append(str(file))
            else:
                dirs_to_remove.append(str(file))

print("dirs_to_remove:")
print("\n".join(dirs_to_remove))

print()

print("files_to_remove:")
print("\n".join(files_to_remove))

print()

response = input("[y/n] Confirm? ")

if response == 'y':
    for dir_to_remove in dirs_to_remove:
        shutil.rmtree(dir_to_remove)

    for file_to_remove in files_to_remove:
        Path(file_to_remove).unlink()