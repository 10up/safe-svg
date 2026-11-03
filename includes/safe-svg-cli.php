<?php
/**
 * Safe SVG WP-CLI commands.
 *
 * @package safe-svg
 */

namespace SafeSvg;

use SafeSvg\safe_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WP-CLI commands for Safe SVG.
 */
class safe_svg_cli {

	/**
	 * Regenerate the metadata for SVG attachments.
	 *
	 * The metadata of an SVG attachment contains a snapshot of the image sizes
	 * registered by the theme at upload time. Use this command to rebuild it
	 * after changing the theme or the image size settings.
	 *
	 * ## OPTIONS
	 *
	 * [<attachment_ids>...]
	 * : One or more attachment IDs. If omitted, all SVG attachments are processed.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation prompt when processing all SVG attachments.
	 *
	 * ## EXAMPLES
	 *
	 *     # Regenerate the metadata for all SVG attachments.
	 *     $ wp safe-svg regenerate-metadata --yes
	 *
	 *     # Regenerate the metadata for the given attachments.
	 *     $ wp safe-svg regenerate-metadata 123 456
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 *
	 * @subcommand regenerate-metadata
	 *
	 * @return void
	 */
	public function regenerate_metadata( $args, $assoc_args ) {
		$safe_svg = new safe_svg();

		if ( ! empty( $args ) ) {
			$this->regenerate_metadata_for_ids( array_map( 'absint', $args ), $safe_svg );
			return;
		}

		$total = $this->get_svg_attachment_count();

		if ( ! $total ) {
			\WP_CLI::success( 'No SVG attachments found.' );
			return;
		}

		\WP_CLI::confirm( sprintf( 'Regenerate the metadata for %d SVG attachments. Continue?', $total ), $assoc_args );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Regenerating metadata', $total );
		$batch    = 50;
		$offset   = 0;
		$counts   = array(
			'regenerated' => 0,
			'skipped'     => 0,
		);

		do {
			$attachment_ids = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_mime_type' => 'image/svg+xml',
					'post_status'    => 'any',
					'fields'         => 'ids',
					'posts_per_page' => $batch,
					'offset'         => $offset,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				)
			);

			$batch_count = count( $attachment_ids );

			foreach ( $attachment_ids as $attachment_id ) {
				if ( $safe_svg->regenerate_metadata( $attachment_id ) ) {
					++$counts['regenerated'];
				} else {
					++$counts['skipped'];
				}

				$progress->tick();
			}

			$offset += $batch;
		} while ( $batch_count === $batch );

		$progress->finish();

		\WP_CLI::success(
			sprintf(
				'Regenerated metadata for %1$d of %2$d SVG attachments (%3$d skipped).',
				$counts['regenerated'],
				$total,
				$counts['skipped']
			)
		);
	}

	/**
	 * Regenerate the metadata for a list of attachment IDs.
	 *
	 * @param array    $attachment_ids The attachment IDs to process.
	 * @param safe_svg $safe_svg       The safe_svg instance.
	 *
	 * @return void
	 */
	private function regenerate_metadata_for_ids( $attachment_ids, $safe_svg ) {
		$counts = array(
			'regenerated' => 0,
			'skipped'     => 0,
		);

		foreach ( $attachment_ids as $attachment_id ) {
			if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
				\WP_CLI::warning( sprintf( 'Attachment %d is not an SVG. Skipping.', $attachment_id ) );
				++$counts['skipped'];
				continue;
			}

			if ( $safe_svg->regenerate_metadata( $attachment_id ) ) {
				\WP_CLI::log( sprintf( 'Regenerated metadata for attachment %d.', $attachment_id ) );
				++$counts['regenerated'];
			} else {
				\WP_CLI::warning( sprintf( 'Could not regenerate the metadata for attachment %d. Skipping.', $attachment_id ) );
				++$counts['skipped'];
			}
		}

		\WP_CLI::success(
			sprintf(
				'Regenerated metadata for %1$d of %2$d attachments (%3$d skipped).',
				$counts['regenerated'],
				count( $attachment_ids ),
				$counts['skipped']
			)
		);
	}

	/**
	 * Count the SVG attachments in the media library.
	 *
	 * @return int The number of SVG attachments.
	 */
	private function get_svg_attachment_count() {
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/svg+xml',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);

		return (int) $query->found_posts;
	}
}
