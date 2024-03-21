import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import {
	BaseControl,
	Panel,
	PanelBody,
	PanelRow,
	Placeholder,
	TextControl,
	TextareaControl,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { Search } from './components';
import { Critic } from './icons';

import './editor.scss';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	const updateMovie = ( property, value ) =>
		setAttributes( { movie: { ... attributes.movie, [ property ]: value } } );

	return (
		<div { ...blockProps }>
			<Placeholder
				icon={ Critic }
				label="Critique"
				instructions="Utilisez ce bloc pour insérer une critique de film."
			>
				<BaseControl
					label="Contenu de la critique"
				>
					<InnerBlocks />
				</BaseControl>
				<Panel>
					<PanelBody title="Détails du film">
						<PanelRow>
							<Search
								attributes={ attributes }
								setAttributes={ setAttributes }
							/>
							<TextControl
								label="ID"
								type="number"
								value={ attributes.movie?.id }
								onChange={ value => updateMovie( 'id', value ) }
							/>
							<TextControl
								label="Titre"
								type="text"
								value={ attributes.movie?.title }
								onChange={ value => updateMovie( 'title', value ) }
							/>
							<TextControl
								label="Date de sortie"
								type="datetime"
								value={ attributes.movie?.release_date }
								onChange={ value => updateMovie( 'release_date', value ) }
							/>
							<TextControl
								label="Durée"
								type="number"
								step={ 1 }
								value={ attributes.movie?.runtime }
								onChange={ value => updateMovie( 'runtime', value ) }
							/>
							<TextControl
								label="Genres"
								type="text"
								value={ attributes.movie?.genres }
								onChange={ value => updateMovie( 'genres', value ) }
							/>
							<TextControl
								label="Réalisation"
								type="text"
								value={ attributes.movie?.director }
								onChange={ value => updateMovie( 'director', value ) }
							/>
							<TextareaControl
								label="Acteurs"
								type="text"
								value={ attributes.movie?.cast }
								onChange={ value => updateMovie( 'cast', value ) }
							/>
							<TextControl
								label="Affiche"
								type="text"
								value={ attributes.movie?.poster_path }
								onChange={ value => updateMovie( 'poster_path', value ) }
							/>
							<TextControl
								label="Aperçu"
								type="text"
								value={ attributes.movie?.backdrop_path }
								onChange={ value => updateMovie( 'backdrop_path', value ) }
							/>
							<TextareaControl
								label="Résumé"
								type="text"
								value={ attributes.movie?.overview }
								onChange={ value => updateMovie( 'overview', value ) }
							/>
						</PanelRow>
					</PanelBody>
				</Panel>
			</Placeholder>
		</div>
	);
};

export default Edit;
