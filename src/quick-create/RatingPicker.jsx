import { BaseControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const StarFull = () => (
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="24" height="24">
		<path d="M10 1l3 6 6 .75-4.12 4.62L16 19l-6-3-6 3 1.13-6.63L1 7.75 7 7z" />
	</svg>
);

const StarHalf = () => (
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="24" height="24">
		<path d="M10 1L7 7l-6 .75 4.13 4.62L4 19l6-3 6 3-1.12-6.63L19 7.75 13 7zm0 2.24l2.34 4.69 4.65.58-3.18 3.56.87 5.15L10 14.88V3.24z" />
	</svg>
);

const StarEmpty = () => (
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="24" height="24">
		<path d="M10 1L7 7l-6 .75 4.13 4.62L4 19l6-3 6 3-1.12-6.63L19 7.75 13 7zm0 2.24l2.34 4.69 4.65.58-3.18 3.56.87 5.15L10 14.88l-4.68 2.34.87-5.15-3.18-3.56 4.65-.58z" />
	</svg>
);

/**
 * RATING_VALUES maps each clickable half-star zone to its rating value.
 * Index 0 = left half of star 1 (0.5), index 1 = right half of star 1 (1.0), etc.
 */
const RATING_VALUES = [
	0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0,
];

/**
 * Interactive star-rating picker (0.5 increments, 1–5 stars).
 *
 * @param {Object}   props
 * @param {number}   props.value    Current rating (0, 0.5, 1.0 … 5.0)
 * @param {Function} props.onChange Called with the new numeric rating value
 */
const RatingPicker = ( { value, onChange } ) => {
	const [ hoverValue, setHoverValue ] = useState( 0 );

	const displayValue = hoverValue || value || 0;

	const renderStars = () => {
		const stars = [];
		for ( let star = 1; star <= 5; star++ ) {
			const fullThreshold = star;
			const halfThreshold = star - 0.5;

			let StarIcon;
			if ( displayValue >= fullThreshold ) {
				StarIcon = StarFull;
			} else if ( displayValue >= halfThreshold ) {
				StarIcon = StarHalf;
			} else {
				StarIcon = StarEmpty;
			}

			stars.push(
				<span key={ star } className="rating-star">
					{ /* Left half = x.5 */ }
					<span
						className="rating-star-zone rating-star-left"
						onMouseEnter={ () => setHoverValue( halfThreshold ) }
						onClick={ () => {
							onChange( value === halfThreshold ? 0 : halfThreshold );
						} }
					/>
					{ /* Right half = x.0 */ }
					<span
						className="rating-star-zone rating-star-right"
						onMouseEnter={ () => setHoverValue( fullThreshold ) }
						onClick={ () => {
							onChange( value === fullThreshold ? 0 : fullThreshold );
						} }
					/>
					<StarIcon />
				</span>
			);
		}
		return stars;
	};

	const label = displayValue > 0
		? `${ displayValue.toFixed( 1 ).replace( '.', ',' ) } / 5`
		: '';

	return (
		<BaseControl label="Note" className="rating-picker-control">
			<div className="rating-picker">
				<div
					className="rating-stars"
					onMouseLeave={ () => setHoverValue( 0 ) }
				>
					{ renderStars() }
				</div>
				{ label && <span className="rating-label">{ label }</span> }
			</div>
		</BaseControl>
	);
};

export default RatingPicker;
