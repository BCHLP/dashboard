import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React from 'react';
import { useState, useMemo, useCallback } from 'react';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEchoModel } from "@laravel/echo-react";


// // eslint-disable-next-line patternfly-react/import-tokens-icons
// import { RegionsIcon as Icon1 } from '@patternfly/react-icons';
// // eslint-disable-next-line patternfly-react/import-tokens-icons
// import { FolderOpenIcon as Icon2 } from '@patternfly/react-icons';

import { TbAntenna } from "react-icons/tb";
import { CiServer, CiRouter, CiSettings } from "react-icons/ci";
import { RiLineChartLine } from "react-icons/ri";
import { ChartArea, ChartContainer, ChartGroup, ChartLabel, ChartVoronoiContainer } from '@patternfly/react-charts/victory';
import { Tabs, Tab, TabTitleText, TabTitleIcon } from '@patternfly/react-core';

import {
    ColaLayout,
    DefaultEdge,
    DefaultGroup,
    DefaultNode,
    EdgeStyle,
    GraphComponent,
    ModelKind,
    NodeModel,
    NodeShape,
    SELECTION_EVENT,
    TopologySideBar,
    TopologyView,
    Visualization,
    VisualizationProvider,
    VisualizationSurface,
    withSelection,
    WithSelectionProps
} from '@patternfly/react-topology';
import { ComponentFactory, Graph, Layout, LayoutFactory, Model, Node, NodeStatus } from '@patternfly/react-topology';

interface CustomNodeProps {
    element: Node;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const BadgeColors = [
    {
        name: 'A',
        badgeColor: '#ace12e',
        badgeTextColor: '#0f280d',
        badgeBorderColor: '#486b00'
    },
    {
        name: 'B',
        badgeColor: '#F2F0FC',
        badgeTextColor: '#5752d1',
        badgeBorderColor: '#CBC1FF'
    }
];

const CustomNode: React.FC<CustomNodeProps & WithSelectionProps> = ({ element, onSelect, selected }) => {
    const data = element.getData();
    const Icon = data.icon;
    const badgeColors = BadgeColors.find((badgeColor) => badgeColor.name === data.badge);

    return (
        <DefaultNode
            element={element}
            showStatusDecorator
            badge={data.badge}
            badgeColor={badgeColors?.badgeColor}
            badgeTextColor={badgeColors?.badgeTextColor}
            badgeBorderColor={badgeColors?.badgeBorderColor}
            onSelect={onSelect}
            selected={selected}
        >
            <g transform={`translate(25, 25)`}>
                <Icon style={{ color: '#393F44' }} size={25} />
            </g>
        </DefaultNode>
    );
};

const customLayoutFactory: LayoutFactory = (type: string, graph: Graph): Layout | undefined => {
    switch (type) {
        case 'Cola':
            return new ColaLayout(graph);
        default:
            return new ColaLayout(graph, { layoutOnDrag: false });
    }
};

const customComponentFactory: ComponentFactory = (kind: ModelKind, type: string) => {
    switch (type) {
        case 'group':
            return DefaultGroup;
        default:
            switch (kind) {
                case ModelKind.graph:
                    return GraphComponent;
                case ModelKind.node:
                    return withSelection()(CustomNode);
                case ModelKind.edge:
                    return withSelection()(DefaultEdge);
                default:
                    return undefined;
            }
    }
};

const NODE_DIAMETER = 75;

const EDGES = [
    // {
    //     id: 'edge-node-4-node-5',
    //     type: 'edge',
    //     source: 'router-0',
    //     target: 'server-0',
    //     edgeStyle: EdgeStyle.default
    // },
    // {
    //     id: 'edge-node-0-node-2',
    //     type: 'edge',
    //     source: 'router-0',
    //     target: 'server-1',
    //     edgeStyle: EdgeStyle.default
    // }
];

type Sensor = {
    id: number;
    name: string;
    metrics: Metric[];
}

type Server = {
    id: number;
    name: string;
    metrics: Metric[];
}

type Router = {
    id: number;
    name: string;
    metrics: Metric[];
}

type Metric = {
    id: number;
    name: string;
}

type Datapoint = {
    x: number;
    y: number;
    name: string;
    node_id: number;
    metric_id: number;
};

type Props = {
    sensors: Sensor[];
    servers: Server[];
    routers: Router[];
    datapoints:Datapoint[];
}

export default function Dashboard (props:Props) {
    const [selectedIds, setSelectedIds] = useState<string[]>([]);
    const [activeTabKey, setActiveTabKey] = useState<string | number>(0);
    const [datapoints, setDatapoints] = useState<Datapoint[]>(props.datapoints);
    // Toggle currently active tab
    const handleTabClick = (
        event: React.MouseEvent<any> | React.KeyboardEvent | MouseEvent,
        tabIndex: string | number
    ) => {
        setActiveTabKey(tabIndex);
    };

    useEchoModel("App.Models.Node", "1", ["App\\Events\\DatapointCreatedEvent"], (e) => {
        console.log(e);
        setDatapoints(prev => {
            const newArray = [...prev, e];
            return newArray.length > 60 ? newArray.slice(1) : newArray;
        });

    });

    const NODES: NodeModel[] = useMemo(() => [
        // {
        //     id: 'server-0',
        //     type: 'node',
        //     label: 'MQTT Proxy',
        //     width: NODE_DIAMETER,
        //     height: NODE_DIAMETER,
        //     shape: NodeShape.ellipse,
        //     status: NodeStatus.danger,
        //     data: {
        //         badge: 'B',
        //         icon: CiServer,
        //     }
        // },
        // {
        //     id: 'router-0',
        //     type: 'node',
        //     label: 'Router',
        //     width: NODE_DIAMETER,
        //     height: NODE_DIAMETER,
        //     shape: NodeShape.hexagon,
        //     status: NodeStatus.warning,
        //     data: {
        //         badge: 'B',
        //         icon: CiRouter
        //     }
        // },
        // {
        //     id: 'server-1',
        //     type: 'node',
        //     label: 'SCADA',
        //     width: NODE_DIAMETER,
        //     height: NODE_DIAMETER,
        //     shape: NodeShape.octagon,
        //     status: NodeStatus.success,
        //     data: {
        //         badge: 'A',
        //         icon: CiServer,
        //     }
        // },
        // {
        //     id: 'Group-1',
        //     children: ['server-0', 'router-0', 'server-1'],
        //     type: 'group',
        //     group: true,
        //     label: 'Group-1',
        //     style: {
        //         padding: 40
        //     }
        // },
        ...props.servers.map((server, index) => ({

            id: server.id,
            type: 'node',
            label: server.name,
            width: NODE_DIAMETER,
            height: NODE_DIAMETER,
            shape: NodeShape.rhombus,
            status: NodeStatus.info,
            data: {
                badge: 'A',
                icon: CiServer,
                metrics: server.metrics,
            }
        }))

    ], [props.servers]);

    const controller = useMemo(() => {
        const model: Model = {
            nodes: NODES,
            edges: EDGES,
            graph: {
                id: 'g1',
                type: 'graph',
                layout: 'Cola'
            }
        };

        const newController = new Visualization();
        newController.registerLayoutFactory(customLayoutFactory);
        newController.registerComponentFactory(customComponentFactory);

        newController.addEventListener(SELECTION_EVENT, setSelectedIds);

        newController.fromModel(model, false);

        return newController;
    }, [NODES]);

    const topologySideBar = (
        <TopologySideBar
            className="topology-example-sidebar"
            show={selectedIds.length > 0}
            onClose={() => setSelectedIds([])}
        >
            <div style={{ marginTop: 27, marginLeft: 20, height: '800px' }}>
                <h1>{NODES.find((node) => node.id === selectedIds[0])?.label}</h1>
                <Tabs
                    isFilled
                    activeKey={activeTabKey}
                    onSelect={handleTabClick}
                    aria-label="Tabs in the filled with icons example"
                    role="region"
                >
                    <Tab
                        eventKey={0}
                        title={
                            <>
                                <TabTitleIcon>
                                    <RiLineChartLine />
                                </TabTitleIcon>{' '}
                                <TabTitleText>Usage</TabTitleText>{' '}
                            </>
                        }
                        aria-label="filled tabs with icons content users"
                    >

                        {NODES.find((node) => node.id === selectedIds[0])?.data.metrics.map((metric:Metric) => (
                            <div key={metric.id}>
                                <div>
                                    <ChartGroup
                                        ariaDesc={metric.name}
                                        containerComponent={<ChartVoronoiContainer labels={({ datum }) => `${datum.name}: ${datum.y}`} constrainToVisibleArea />}
                                        height={100}
                                        maxDomain={{y: 100}}
                                        name={"chart"+metric.id}
                                        padding={0}
                                        width={400}
                                    >
                                        <ChartArea
                                            data={datapoints.filter((datapoint) => datapoint.node_id == selectedIds[0] && datapoint.metric_id == metric.id)}
                                        />
                                    </ChartGroup>
                                </div>
                            </div>
                        ))}


                    </Tab>
                    <Tab
                        eventKey={1}
                        title={
                            <>
                                <TabTitleIcon>
                                    <CiSettings />
                                </TabTitleIcon>{' '}
                                <TabTitleText>Configuration</TabTitleText>{' '}
                            </>
                        }
                    >
                        <div style={{ marginLeft: '50px', marginTop: '50px', height: '135px' }}>
                            The configuration layout will go here

                        </div>
                    </Tab>
                </Tabs>



            </div>
        </TopologySideBar>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div
                    className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <TopologyView sideBar={topologySideBar}>
                        <VisualizationProvider controller={controller}>
                            <VisualizationSurface state={{ selectedIds }} />
                        </VisualizationProvider>
                    </TopologyView>
                </div>
            </div>
        </AppLayout>
    );
};
