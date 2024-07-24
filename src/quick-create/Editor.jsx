import { BaseControl, Button, ComboboxControl, Flex, FlexBlock, Modal, Notice, SelectControl, TextControl, TimePicker } from '@wordpress/components';
import { useDebounce } from '@wordpress/compose';
import { store as coreStore } from '@wordpress/core-data';
import { dispatch, useSelect } from '@wordpress/data';
import { date } from '@wordpress/date';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { closeSmall } from '@wordpress/icons';

const EpisodeEditor = ( { data, setData } ) => {
    const [ search, setSearch ] = useState( '' );
	const [ termID, setTermID ] = useState( 0 );

	const debouncedSearch = useDebounce( setSearch, 500 );

	const { searchResults } = useSelect(
		( select ) => {
			let searchResults = [];
			if ( search ) {
				searchResults =
					select( coreStore ).getEntityRecords( 'taxonomy', 'series', {
						per_page: 10,
                        show_empty: true,
						context: 'view',
						search,
					} ) ?? [];
			}
			return { searchResults };
		},
		[ search ]
	);

	const suggestions = useMemo( () => {
		return ( searchResults ?? [] ).map( ( term ) => ( {
			value: parseInt( term.id ),
			label: decodeEntities( term.name ),
		} ) );
	}, [ searchResults ] );

	const { term } = useSelect(
		( select ) => {
			if ( termID ) {
				const term = select( coreStore ).getEntityRecord(
                    'taxonomy',
                    'series',
                    termID,
                    {
                        _embed: 1,
                        context: 'view',
                    }
                ) ?? {};
				return { term };
			}
			return {};
		},
		[ termID ]
	);

    const { parent } = useSelect(
        ( select ) => {
            if ( term?.parent ) {
                const parent = select( coreStore ).getEntityRecord( 'taxonomy', 'series', term.parent );
                return { parent };
            }
            return {};
        }, [ term ]
    );

    const { backdrop } = useSelect(
        ( select ) => {
            if ( term?.meta?.series_image ) {
                const backdrop = select( coreStore ).getEntityRecord(
                    'postType',
                    'attachment',
                    term.meta.series_image
                );
                return { backdrop };
            }
            return {};
        },
        [ term ]
    );

    useEffect( () => {
        if ( term && '' !== term.name ) {
            const matches = term.name.match( /(Saison ([0-9]{1,2}))/i );
            if ( 3 === matches.length ) {
                setData( { ...data, season: parseInt( matches[ 2 ] ) } );
            }
        }
        return () => {};
    }, [ term ] );

    useEffect( () => {
        if ( parent && '' !== parent.name ) {
            setData( { ...data, title: parent.name } );
        }
        return () => {};
    }, [ parent ] );

    return (
        <>
            { ! term?.id ? (
                <ComboboxControl
                    label="Série"
                    value={ term?.name ?? '' }
                    onChange={ ( value ) => {
                        setTermID( value );
                        setSearch( '' );
                        setData( { ...data, series: value } );
                    } }
                    options={ suggestions }
                    onFilterValueChange={ debouncedSearch }

                />
            ) : (
				<>
					<BaseControl
						id={ `term-preview-${ term.id }` }
						label="Série"
					>
                        <div className="term-preview">
                            <a
                                href={ `/wp-admin/term.php?taxonomy=series&tag_ID=${ term.id }` }
                                target="_blank"
                            >
                                { backdrop && (
                                    <img
                                        src={
                                            backdrop.media_details?.sizes
                                                ?.thumbnail?.source_url
                                        }
                                        alt={
                                            backdrop.media_details?.image_meta
                                                ?.caption ?? ''
                                        }
                                    />
                                ) }
                                <div>
                                    <div className="title">{ term.name }</div>
                                    <div className="content">
                                        { term.description } − { `${ term.count } épisode${ 1 < term.count ? 's' : '' }` }
                                    </div>
                                </div>
                            </a>
                            <Button
                                icon={ closeSmall }
                                variant="tertiary"
                                onClick={ () => {
                                    setTermID( 0 );
                                    setSearch( '' );
                                    setData( { ...data, series: null } );
                                } }
                            />
                        </div>
					</BaseControl>
				</>
			) }
            <Flex>
                <FlexBlock>
                    <TextControl
                        label="Saison"
                        value={ data.season }
                        type="number"
                        max={ 999 }
                        min={ 1 }
                        step={ 1 }
                        onChange={ ( value ) => setData( { ...data, season: value } ) }
                    />
                </FlexBlock>
                <FlexBlock>
                    <TextControl
                        label="Épisode"
                        value={ data.episode }
                        type="number"
                        max={ 999 }
                        min={ 1 }
                        step={ 1 }
                        onChange={ ( value ) => setData( { ...data, episode: value } ) }
                    />
                </FlexBlock>
            </Flex>
            <SelectControl
                label="Date"
                value={ data.date }
                onChange={ ( value ) => setData( { ...data, date: value } ) }
                options={ [
                    { value: 'tonight', label: 'Ce soir' },
                    { value: 'yesterday', label: 'Hier' },
                    { value: 'd-2', label: 'Avant-hier' },
                    { value: 'custom', label: 'Date personnalisée' },
                ] }
            />
            { 'custom' === data.date && (
                <TimePicker
                    currentDate={ data.datetime }
                    onChange={ ( newDate ) => setData( { ...data, datetime: newDate } ) }
                    style={ { display: 'flex' } }
                />
            ) }
        </>
    );
};

const MovieEditor = ( { data, setData } ) => {
    return (
        <>
            <TextControl
                label="Titre"
                value={ data.title }
                onChange={ ( value ) => setData( { ...data, title: value } ) }
            />
            <SelectControl
                label="Date"
                value={ data.date }
                onChange={ ( value ) => setData( { ...data, date: value } ) }
                options={ [
                    { value: 'tonight', label: 'Ce soir' },
                    { value: 'yesterday', label: 'Hier' },
                    { value: 'd-2', label: 'Avant-hier' },
                    { value: 'custom', label: 'Date personnalisée' },
                ] }
            />
            { 'custom' === data.date && (
                <TimePicker
                    currentDate={ data.datetime }
                    onChange={ ( newDate ) => setData( { ...data, datetime: newDate } ) }
                    style={ { display: 'flex' } }
                />
            ) }
        </>
    );
};

const Editor = ( { type } ) => {
    const [ isOpen, setIsOpen ] = useState( false );
    const openModal = () => setIsOpen( true );
    const closeModal = () => setIsOpen( false );

    const [ saving, setSaving ] = useState( false );
    const [ error, setError ] = useState( null );

    const createRecord = async () => {

        setSaving( true );

        let datetime = new Date();
        if ( 'custom' === data.date ) {
            datetime = data.datetime;
        } else if ( 'today' === data.date ) {
            datetime = datetime.setHours( 20, 30, 0, 0 );
        } else if ( 'yesterday' === data.date ) {
            datetime = ( new Date( datetime.setDate( datetime.getDate() - 1 ) ) ).setHours( 20, 30, 0, 0 );
        } else if ( 'd-2' === data.date ) {
            datetime = ( new Date( datetime.setDate( datetime.getDate() - 2 ) ) ).setHours( 20, 30, 0, 0 );
        }

        let record = {
            title: data.title,
            date: date( 'Y-m-d H:i:s', datetime ),
            status: 'publish',
        };

        if ( 'movie' === type ) {
            record.categories = [ 11 ];
            record.content = `<!-- wp:paragraph -->\n<p>A vu <em>${ data.title }</em></p>\n<!-- /wp:paragraph -->`;
        } else if ( 'episode' === type ) {
            record.title = `${ data.title } ${ data.season }×${ data.episode }`;
            record.categories = [ 1441 ];
            record.series = [ data.series ];
            record.content = `<!-- wp:paragraph -->\n<p>A vu <em>${ data.title }</em>, saison ${ data.season }, épisode ${ data.episode }</p>\n<!-- /wp:paragraph -->`;
        }

        const newRecord = await dispatch( 'core' ).saveEntityRecord( 'postType', 'post', record );
        if ( newRecord ) {
            closeModal();
            window.location.reload();
        } else {
            setError( 'Une erreur s’est produite lors de la création de l’article.' );
        }
    };

    const [ data, setData ] = useState( {
        title: '',
        date: 'tonight',
        datetime: ( new Date() ).setHours( 22, 30, 0, 0 ),
        series: null,
        season: null,
        episode: null
    } );

    return (
        <>
            <button
                type="button"
                className="page-title-action"
                onClick={ openModal }
            >
                { 'movie' === type ? 'Ajouter un film' : 'Ajouter un épisode' }
            </button>
            { isOpen && (
                <Modal
                    title={ `Ajouter un ${ 'movie' === type ? 'film' : 'épisode' }` }
                    onRequestClose={ closeModal }
                    className={ `quick-editor-modal ${ type }-editor-modal` }
                >
                    { 'movie' === type ? (
                        <MovieEditor
                            data={ data }
                            setData={ setData }
                        />
                    ) : (
                        <EpisodeEditor
                            data={ data }
                            setData={ setData }
                        />
                    ) }
                    <hr style={ { margin: '20px 0' } } />
                    { error && (
                        <Notice
                            status="error"
                            isDismissible={ false }
                        >
                            { error }
                        </Notice>
                    ) }
                    <Button
                        variant="primary"
                        onClick={ createRecord }
                        disabled={ saving }
                    >
                        Créer { 'movie' === type ? 'le film' : 'l\'épisode' }
                    </Button>
                </Modal>
            ) }
        </>
    );
};

export default Editor;