import {
	BaseControl,
	Button,
	Flex,
	FlexBlock,
	Notice,
	SearchControl,
	SelectControl,
	Spinner,
	TimePicker,
} from '@wordpress/components';
import { useDebounce } from '@wordpress/compose';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useEffect, useRef, useState } from '@wordpress/element';
import { closeSmall, crop as cropIcon } from '@wordpress/icons';

import ImageCropper from './ImageCropper';
import RatingPicker from './RatingPicker';

const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p';

/**
 * TMDb movie search + backdrop picker + crop + duplicate check.
 */
const MovieEditor = ( { data, setData } ) => {
	const APIKEY = window.roxaneOptions?.tmdb_api_key ?? '';
	const LOCALE = window.roxaneOptions?.locale ?? 'fr-FR';

	// Search state
	const [ search, setSearch ] = useState( '' );
	const [ results, setResults ] = useState( [] );
	const [ searching, setSearching ] = useState( false );

	// Selected movie state
	const [ movie, setMovie ] = useState( null );

	// Backdrops state
	const [ backdrops, setBackdrops ] = useState( [] );
	const [ loadingBackdrops, setLoadingBackdrops ] = useState( false );
	const [ selectedBackdrop, setSelectedBackdrop ] = useState( null );

	// Crop state
	const [ showCropper, setShowCropper ] = useState( false );
	const [ croppedBlob, setCroppedBlob ] = useState( null );
	const [ croppedPreview, setCroppedPreview ] = useState( null );

	// Duplicate check state
	const [ duplicateChecked, setDuplicateChecked ] = useState( false );
	const [ isDuplicate, setIsDuplicate ] = useState( false );

	/**
	 * Search TMDb for movies.
	 */
	const doSearch = async () => {
		if ( '' === search ) {
			setResults( [] );
			return;
		}
		setSearching( true );
		try {
			const response = await fetch(
				`https://api.themoviedb.org/3/search/movie?query=${ encodeURIComponent( search ) }&api_key=${ APIKEY }&language=${ LOCALE }`
			);
			const json = await response.json();
			setResults( json.results ?? [] );
		} catch ( e ) {
			console.error( e );
		}
		setSearching( false );
	};

	const debouncedSearch = useDebounce( doSearch, 500 );

	useEffect( () => {
		debouncedSearch();
	}, [ search ] );

	/**
	 * Select a movie from search results.
	 */
	const selectMovie = async ( id ) => {
		setResults( [] );
		setSearch( '' );
		setLoadingBackdrops( true );

		try {
			// Fetch movie details
			const detailRes = await fetch(
				`https://api.themoviedb.org/3/movie/${ id }?api_key=${ APIKEY }&language=${ LOCALE }`
			);
			const movieData = await detailRes.json();
			setMovie( movieData );
			setData( ( prev ) => ( {
				...prev,
				title: movieData.title,
				tmdbId: movieData.id,
				backdropPath: null,
			} ) );

			// Fetch images (backdrops)
			const imgRes = await fetch(
				`https://api.themoviedb.org/3/movie/${ id }/images?api_key=${ APIKEY }`
			);
			const imgData = await imgRes.json();
			setBackdrops( imgData.backdrops ?? [] );
		} catch ( e ) {
			console.error( e );
		}

		setLoadingBackdrops( false );
	};

	/**
	 * Check for duplicate posts when movie is selected,
	 * using the standard WP REST API posts endpoint.
	 */
	const { existingPosts } = useSelect(
		( select ) => {
			if ( ! movie?.title ) {
				return {};
			}
			const existingPosts =
				select( coreStore ).getEntityRecords( 'postType', 'post', {
					per_page: 1,
					categories: [ 11 ],
					search: movie.title,
					status: 'publish',
					_fields: 'id',
				} ) ?? [];
			return { existingPosts };
		},
		[ movie?.title ]
	);

	useEffect( () => {
		if ( ! movie?.title ) {
			setDuplicateChecked( false );
			setIsDuplicate( false );
			return;
		}

		if ( existingPosts !== undefined ) {
			const found = existingPosts.length > 0;
			setIsDuplicate( found );
			setDuplicateChecked( true );
			setData( ( prev ) => ( {
				...prev,
				rewatch: found,
			} ) );
		}
	}, [ existingPosts, movie ] );

	/**
	 * Clear movie selection.
	 */
	const clearMovie = () => {
		setMovie( null );
		setBackdrops( [] );
		setSelectedBackdrop( null );
		setCroppedBlob( null );
		setCroppedPreview( null );
		setShowCropper( false );
		setDuplicateChecked( false );
		setIsDuplicate( false );
		setData( ( prev ) => ( {
			...prev,
			title: '',
			tmdbId: null,
			backdropPath: null,
			croppedImage: null,
			rewatch: false,
		} ) );
	};

	/**
	 * Pick a backdrop.
	 */
	const pickBackdrop = ( backdrop ) => {
		setSelectedBackdrop( backdrop );
		setCroppedBlob( null );
		setCroppedPreview( null );
		setShowCropper( true );
		setData( ( prev ) => ( {
			...prev,
			backdropPath: backdrop.file_path,
		} ) );
	};

	/**
	 * Handle crop complete.
	 */
	const handleCropComplete = ( blob ) => {
		setCroppedBlob( blob );
		const url = URL.createObjectURL( blob );
		setCroppedPreview( url );
		setShowCropper( false );

		setData( ( prev ) => ( {
			...prev,
			croppedImage: blob,
		} ) );
	};

	/**
	 * Year from release_date.
	 */
	const releaseYear = ( releaseDate ) => {
		if ( ! releaseDate ) return '';
		return releaseDate.substring( 0, 4 );
	};

	return (
		<>
			{ /* ── Movie search ── */ }
			{ ! movie ? (
				<div className="movie-search">
					<SearchControl
						__nextHasNoMarginBottom
						label="Film"
						placeholder="Rechercher un film…"
						value={ search }
						onChange={ setSearch }
					/>
					{ searching && <Spinner /> }
					{ results.length > 0 && (
						<div className="movie-search-results">
							{ results.map( ( result ) => (
								<a
									key={ result.id }
									href="#"
									className="movie-search-result"
									onClick={ ( e ) => {
										e.preventDefault();
										selectMovie( result.id );
									} }
								>
									{ result.poster_path ? (
										<img
											src={ `${ TMDB_IMAGE_BASE }/w92${ result.poster_path }` }
											alt={ result.title }
										/>
									) : (
										<span className="no-poster" />
									) }
									<span className="details">
										<span className="title">{ result.title }</span>
										<span className="year">{ releaseYear( result.release_date ) }</span>
									</span>
								</a>
							) ) }
						</div>
					) }
				</div>
			) : (
				<>
					{ /* ── Selected movie preview ── */ }
					<BaseControl
						id={ `movie-preview-${ movie.id }` }
						label="Film"
					>
						<div className="movie-preview">
							<a
								href={ `https://www.themoviedb.org/movie/${ movie.id }` }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ movie.poster_path && (
									<img
										src={ `${ TMDB_IMAGE_BASE }/w92${ movie.poster_path }` }
										alt={ movie.title }
									/>
								) }
								<div>
									<div className="title">{ movie.title }</div>
									<div className="content">
										{ releaseYear( movie.release_date ) }
										{ movie.runtime ? ` − ${ movie.runtime } min` : '' }
									</div>
								</div>
							</a>
							<Button
								icon={ closeSmall }
								variant="tertiary"
								onClick={ clearMovie }
							/>
						</div>
					</BaseControl>

					{ /* ── Duplicate check notice ── */ }
					{ duplicateChecked && isDuplicate && (
						<Notice
							status="info"
							isDismissible={ false }
						>
							Déjà vu ! Le contenu sera « A revu <em>{ movie.title }</em> ».
						</Notice>
					) }

					{ /* ── Backdrop picker ── */ }
					{ loadingBackdrops && <Spinner /> }
					{ backdrops.length > 0 && ! croppedPreview && (
						<BaseControl label="Backdrop">
							<div className="backdrop-picker">
								{ backdrops.slice( 0, 12 ).map( ( backdrop, i ) => (
									<button
										key={ i }
										type="button"
										className={ `backdrop-option${ selectedBackdrop?.file_path === backdrop.file_path ? ' selected' : '' }` }
										onClick={ () => pickBackdrop( backdrop ) }
									>
										<img
											src={ `${ TMDB_IMAGE_BASE }/w300${ backdrop.file_path }` }
											alt={ `Backdrop ${ i + 1 }` }
										/>
									</button>
								) ) }
							</div>
						</BaseControl>
					) }

					{ /* ── Image cropper ── */ }
					{ showCropper && selectedBackdrop && (
						<ImageCropper
							imageUrl={ `${ TMDB_IMAGE_BASE }/original${ selectedBackdrop.file_path }` }
							onCropComplete={ handleCropComplete }
							onCancel={ () => setShowCropper( false ) }
						/>
					) }

					{ /* ── Cropped preview ── */ }
					{ croppedPreview && (
						<BaseControl label="Image sélectionnée">
							<div className="cropped-preview">
								<img src={ croppedPreview } alt="Image recadrée" />
								<Button
									icon={ cropIcon }
									variant="secondary"
									size="small"
									onClick={ () => {
										setCroppedBlob( null );
										setCroppedPreview( null );
										setShowCropper( true );
										setData( ( prev ) => ( {
											...prev,
											croppedImage: null,
										} ) );
									} }
								>
									Recadrer
								</Button>
							</div>
						</BaseControl>
					) }
				</>
			) }

			{ /* ── Rating picker ── */ }
			<RatingPicker
				value={ data.rating }
				onChange={ ( value ) => setData( ( prev ) => ( { ...prev, rating: value } ) ) }
			/>

			{ /* ── Date picker ── */ }
			<SelectControl
				__next40pxDefaultSize={ true }
				label="Date"
				value={ data.date }
				onChange={ ( value ) => setData( ( prev ) => ( { ...prev, date: value } ) ) }
				options={ [
					{ value: 'tonight', label: 'Ce soir' },
					{ value: 'yesterday', label: 'Hier' },
					{ value: 'd-2', label: 'Avant-hier' },
					{ value: 'custom', label: 'Date personnalisée' },
				] }
			/>
			{ 'custom' === data.date && (
				<TimePicker
					__next40pxDefaultSize={ true }
					currentDate={ data.datetime }
					onChange={ ( newDate ) => setData( ( prev ) => ( { ...prev, datetime: newDate } ) ) }
					style={ { display: 'flex' } }
				/>
			) }
		</>
	);
};

export default MovieEditor;
