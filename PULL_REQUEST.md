# Fix ArgumentCountError in get_image_tag_override method

## Problem
The plugin was throwing a fatal error when the `get_image_tag_override` method was called with fewer than the expected 6 arguments:

```
Fatal error: Uncaught ArgumentCountError: Too few arguments to function SafeSvg\safe_svg::get_image_tag_override(), 2 passed in /var/www/html/wp-includes/class-wp-hook.php on line 324 and exactly 6 expected
```

## Root Cause Analysis
1. **Incorrect Hook Type**: The `get_image_tag` was registered as an action instead of a filter
2. **Rigid Parameter Requirements**: The method required exactly 6 parameters with no defaults
3. **No Fallback Handling**: There was no graceful handling when fewer arguments were provided

## Solution
This PR implements a defensive programming approach to handle edge cases while maintaining full backward compatibility:

### 1. Corrected Hook Registration
```php
// Before: Incorrect action registration
add_action( 'get_image_tag', array( $this, 'get_image_tag_override' ), 10, 6 );

// After: Correct filter registration  
add_filter( 'get_image_tag', array( $this, 'get_image_tag_override' ), 10, 6 );
```

According to [WordPress documentation](https://developer.wordpress.org/reference/hooks/get_image_tag/), `get_image_tag` is a filter that modifies HTML content, not an action.

### 2. Made Parameters Optional with Sensible Defaults
```php
// Before: Required all 6 parameters
public function get_image_tag_override( $html, $id, $alt, $title, $align, $size )

// After: Optional parameters with defaults
public function get_image_tag_override( $html, $id = null, $alt = '', $title = '', $align = '', $size = 'medium' )
```

### 3. Added Early Return Safety Check
```php
// Return early if we don't have a valid attachment ID
if ( ! $id ) {
    return $html;
}
```

This ensures the method gracefully handles cases where:
- Only HTML is passed (edge case scenarios)
- ID is null or invalid
- Different WordPress versions or plugins call the filter differently

## Testing
- ✅ All existing tests continue to pass
- ✅ Added comprehensive test coverage for the new functionality:
  - Handles fewer arguments gracefully
  - Processes SVG files correctly with proper dimensions
  - Removes width/height attributes when no size options exist
  - Maintains accessibility by adding `role="img"`
- ✅ Updated unit tests to reflect the correct filter registration

## Backward Compatibility
- ✅ Existing functionality remains unchanged
- ✅ All existing WordPress filter calls continue to work
- ✅ New defensive handling prevents fatal errors in edge cases

## Benefits
1. **Prevents Fatal Errors**: No more ArgumentCountError crashes
2. **Improved Robustness**: Handles various WordPress environments and plugin interactions
3. **Better Accessibility**: Maintains `role="img"` addition for screen readers
4. **Future-Proof**: Defensive against changes in WordPress core or other plugins

## Files Changed
- `safe-svg.php`: Fixed hook registration and method signature
- `tests/unit/test-safe-svg.php`: Updated tests and added comprehensive coverage

This fix addresses the immediate fatal error while improving the overall reliability of the SVG handling functionality.
