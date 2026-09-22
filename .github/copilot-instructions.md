# NovaCart Copilot instructions

NovaCart is based on OpenCart 3.0.3.9.

## Compatibility

- Keep PHP code compatible with PHP 7.3+ unless a task explicitly changes that requirement.
- Preserve OpenCart 3.x conventions and OCMOD compatibility where practical.
- Do not replace established OpenCart extension points with incompatible patterns without a clear reason.

## User-facing change descriptions

When summarizing changes or generating changelog text:

- Write the final user-facing changelog text in Russian.
- Prefer one concise sentence.
- Describe observable behavior for store administrators, developers, or customers.
- Do not list filenames, commits, branches, function names, or internal refactoring unless that detail materially matters.
- Do not invent behavior that is not evident from the actual diff.
- Formatting-only changes, branch synchronization, CI-only changes, tests, comments, and documentation-only changes should normally be treated as technical and omitted from the user changelog.
- Classify meaningful changes as Added, Changed, Fixed, or Security.
- Security should be used only for a genuine security-related change, not generic validation or cleanup.
