import { SearchControl } from '@wordpress/components';
import { useDebounce } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';

import _ from 'underscore';

const Search = ( { attributes, setAttributes } ) => {
	const APIKEY = window.roxaneOptions?.tmdb_api_key ?? '';
	const LOCALE = window.roxaneOptions?.locale ?? 'en-US';

	const [ search, setSearch ] = useState( '' );
	const [ results, setResults ] = useState( [] );
	const [ loading, setLoading ] = useState( false );

	const selectMovie = async ( id ) => {
		fetch(
			`https://api.themoviedb.org/3/movie/${ id }?api_key=${ APIKEY }&language=${ LOCALE }&append_to_response=credits`
		).then( ( response ) => {
			response
				.json()
				.then( ( data ) => {
					let movie = _.pick(
						data,
						[
							'id',
							'title',
							'release_date',
							'poster_path',
							'backdrop_path',
							'overview',
							'runtime'
						]
					);
					movie.genres = _.pluck( data.genres, 'name' ).join( ', ' );
					movie.cast = _.pluck( data.credits.cast, 'name' ).join( ', ' );
					movie.director = _.chain( data.credits.crew )
						.filter( { job: 'Director' } )
						.pluck( 'name' )
						.value()
						.join( ', ' );
					setAttributes( { movie } );
				} );
		} )
		.catch( ( error ) => {
			console.error( error );
		} );
	
	}

	const searchMovie = async () => {
		if ( '' !== search ) {
			setLoading( true );
			fetch(
				`https://api.themoviedb.org/3/search/movie?query=${ search }&api_key=${ APIKEY }&language=${ LOCALE }`
			).then( ( response ) => {
				response
					.json()
					.then( ( data ) => {
						setResults( data.results );
						setLoading( false );
					} );
			} )
			.catch( ( error ) => {
				console.error( error );
				setLoading( false );
			} );
		} else {
			setResults( [] );
		}
	}

	const debouncedSearch = useDebounce( searchMovie, 500 );

	useEffect( () => {
		debouncedSearch();
		return () => {};
	}, [ search ] );

	return (
		<div className="search-wrapper">
			<div className="search-form">
				<SearchControl
					__nextHasNoMarginBottom
					placeholder={ select( 'core/editor' ).getEditedPostAttribute( 'title' ) }
					value={ search }
					onChange={ setSearch }
				/>
			</div>
			{ results.length ? (
				<div className="search-results">
					{ results.map( ( result, key ) => (
						<a
							key={ key }
							href={ `https://www.themoviedb.org/movie/${ result.id }` }
							target="_blank"
							className="search-result"
							onClick={ ( event ) => {
								event.preventDefault()
								selectMovie( result.id )
								setResults( [] )
							} }
						>
							<img className="search-result__image" src={ `https://image.tmdb.org/t/p/w220_and_h330_face${ result.poster_path }` } alt={ result.title } />
						</a>
					) ) }
				</div>
			) : null }
		</div>
	);
};

export default Search;